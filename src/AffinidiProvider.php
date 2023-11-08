<?php

namespace Affinidi\SocialiteProvider;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Illuminate\Support\Arr;

class AffinidiProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';
    protected $usesPKCE = true;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'openid',
        'offline_access',
    ];

    public function getIssuerUrl()
    {
        return config('services.affinidi.base_uri');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getIssuerUrl() . '/oauth2/auth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->getIssuerUrl() . '/oauth2/token';
    }

    private function extractProps($data, $names)
    {
        $values = [];
        if (!is_array($names)) {
            $names = [$names];
        }
        foreach ($data as $customData) {
            foreach ($names as $name) {
                if (isset($customData[$name])) {
                    $values[$name] = $customData[$name];
                }
            }
        }
        return $values;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        // get the user details from ID token
        $info = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))), true);
        $custom = $info['custom'];
        $values = $this->extractProps($custom, [
            'email',
            'name',
            'givenName',
            'familyName',
            'middleName',
            'nickname',
            'picture',
            'gender',
            'birthdate',
            'phoneNumber',
            'address'
        ]);
        unset($info['custom']);
        $user = array_merge($info, $values);

        return $user;
    }

    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'error' => 'Error: Invalid State',
            ]);
            throw $error;
        }

        $code = $this->getCode();

        if (!isset($code)) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'error' => 'Error : ' . $this->request->input('error') . ' -> ' . $this->request->input('error_description'),
            ]);
            throw $error;
        }

        $response = $this->getAccessTokenResponse($code);

        $user = $this->getUserByToken(Arr::get($response, 'id_token'));

        return $this->userInstance($response, $user);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $user['id'] = Arr::get($user, 'sub');

        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'sub'),
            'nickname' => Arr::get($user, 'nickname'),
            'name' => Arr::get($user, 'name'),
            'email' => Arr::get($user, 'email'),
        ]);
    }
}