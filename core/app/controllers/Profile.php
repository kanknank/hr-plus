<?php
namespace App\Controllers;
use Zoomx\Controllers;

class Profile extends \Zoomx\Controllers\Controller
{
    /**
     * 
     */
    public function get()
    {
        $user = $this->modx->user;
        if (!$this->modx->user->isAuthenticated('web')) {
            return abortx(401);
        }
        
        $data = $user->getOne('Profile')->toArray();
        $ext = $data['extended'] ?? [];
        $data = array_merge($data, $ext);

        if (!empty($data['photo'])) {
            $data['photo'] = $this->modx->getOption('site_url') . $data['photo'];
        }

        return jsonx(['success' => true, 'data' => $data]);
    }


    /**
     * 
     */
    public function update()
    {
        $user = $this->modx->user;
        if (!$this->modx->user->isAuthenticated('web')) {
            return abortx(401);
        }
        
        $profile = $user->getOne('Profile');

        $ext = json_decode($profile->extended, true) ?? [];

        foreach ($_POST as $key => $val) {
            if (in_array($key, ['fullname', 'phone', 'city', 'country'])) {
                $profile->set($key, trim($val));
            } else if (in_array($key, ['surname', 'middlename', 'birthdate', 'work_phone', 'tg', 'vk', 'resume', 'portfolio', 'description'])) {
                $ext[$key] = trim($val);
            }
        }

        $profile->set('extended', json_encode($ext));
        
        if ($_FILES && $_FILES["avatar"]["error"] === UPLOAD_ERR_OK) {
            $path = "assets/userfiles/{$this->modx->user->id}";
            if (!file_exists(MODX_BASE_PATH . $path)) {
                mkdir($path, 0777, true);
            }
            $time = time();
            $path_info = pathinfo($_FILES["avatar"]["name"]);
            $file = "{$path}/avatar-{$time}.{$path_info['extension']}";
            move_uploaded_file($_FILES["avatar"]["tmp_name"], MODX_BASE_PATH . $file);
            $profile->set('photo', $file);
        }

        $save = $profile->save();

        return jsonx(['success' => !!$save, 'data' => []]);
    }


    /**
     * 
     */
    public function change_password()
    {
        if (!$this->modx->user->isAuthenticated('web')) {
            return abortx(401);
        }

        $errors = [];

        $username = trim($_POST['username'] ?? '');
        $old_password = trim($_POST['old_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if ($username !== $this->modx->user->username) {
            $errors['username'] = 'Неверный логин';
        }

        if (mb_strlen($new_password) < 8) {
            $errors['new_password'] = 'Пароль должен содержать не менее 8 символов';
        }

        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }

        if (count($errors)) {
            return jsonx([
                'success' => false,
                'errors' => $errors,
            ]);
        }

        $success = $this->modx->user->changePassword($new_password, $old_password);
        if (!$success) {
            $errors['old_password'] = 'Неверный старый пароль';
        }

        if (count($errors)) {
            return jsonx([
                'success' => false,
                'errors' => $errors,
            ]);
        }
        

        return jsonx(['success' => true, 'data' => []]);
    }
}
