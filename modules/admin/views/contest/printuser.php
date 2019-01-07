<?php
/* @var $users app\models\ContestUser */
?>
<table border="1">
    <tbody>
    <tr><td colspan="3">Copy these accounts to distribute</td></tr>
    <tr>
        <td>name</td>
        <td>login_id</td>
        <td>password</td>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user->user->nickname ?></td>
            <td><?= $user->user->username ?></td>
            <td><?= $user->user_password ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
