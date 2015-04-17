<table border="1">
    <tr>
        <th><?php $this->get_lang('Firstname'); ?></th>
        <th><?php $this->get_lang('Lastname'); ?></th>
    </tr>
 <?php foreach ($this->userList as $user):  ?>
    <tr>
        <td><?php echo $user['firstname']; ?></td>
        <td><?php echo $user['lastname']; ?></td>
    </tr>
<?php endforeach; ?>
</table>