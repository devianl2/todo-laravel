<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository
{

    /**
     * Find user by platform
     * @param string $provider
     * @param string $providerId
     * @return mixed|null
     */
    public function findUserBySocialAuth(string $provider, string $providerId)
    {
        $data = User::where('auth_type', $provider)->where('social_id', $providerId)->first();

        return $data;
    }

    /**
     * Save user into DB
     * @param array $formData
     * @param string|null $id
     * @return User
     */
    public function saveUser(array $formData, string $id = null)
    {
        /**
         * Only update the user data if the formData key is found.
         * For e.g: user change password will only touch password field, hence, formData['password'] is needed
         * This function will reduce query redundancy and easier to maintain database column
         */

        if ($id) {
            $data = User::findOrFail($id);
        } else {
            $data = new User();
        }

        if (array_key_exists('name', $formData)) {
            $data->name = $formData['name'];
        }

        if (array_key_exists('email', $formData)) {
            $data->email = $formData['email'];
        }

        if (array_key_exists('password', $formData)) {
            $data->password = bcrypt($formData['password']);
        }

        if (array_key_exists('socialId', $formData)) {
            $data->social_id = $formData['socialId'];
        }

        if (array_key_exists('authType', $formData)) {
            $data->auth_type = $formData['authType'];
        }

        $data->save();

        return $data;
    }
}
