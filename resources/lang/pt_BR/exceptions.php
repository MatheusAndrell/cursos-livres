<?php

return array (
  'backend' => 
  array (
    'access' => 
    array (
      'roles' => 
      array (
        'already_exists' => 'Essa função já existe. Escolha um nome diferente.',
        'cant_delete_admin' => 'Você não pode excluir a função de administrador.',
        'create_error' => 'Houve um problema ao criar esta função. Por favor, tente novamente.',
        'delete_error' => 'Houve um problema ao excluir esta função. Por favor, tente novamente.',
        'has_users' => 'Você não pode excluir uma função com usuários associados.',
        'needs_permission' => 'Você deve selecionar pelo menos uma permissão para esta função.',
        'not_found' => 'Esse papel não existe.',
        'update_error' => 'Houve um problema ao atualizar esta função. Por favor, tente novamente.',
      ),
      'users' => 
      array (
        'already_confirmed' => 'Este usuário já está confirmado.',
        'cant_confirm' => 'Houve um problema ao confirmar a conta do usuário.',
        'cant_deactivate_self' => 'Você não pode fazer isso consigo mesmo.',
        'cant_delete_admin' => 'Você não pode excluir o superadministrador.',
        'cant_delete_own_session' => 'Você não pode excluir sua própria sessão.',
        'cant_delete_self' => 'Você não pode excluir a si mesmo.',
        'cant_restore' => 'Este usuário não foi excluído, portanto não pode ser restaurado.',
        'cant_unconfirm_admin' => 'Você não pode cancelar a confirmação do superadministrador.',
        'cant_unconfirm_self' => 'Você não pode se desfazer da confirmação.',
        'create_error' => 'Houve um problema ao criar este usuário. Por favor, tente novamente.',
        'delete_error' => 'Houve um problema ao excluir este usuário. Por favor, tente novamente.',
        'delete_first' => 'Este usuário deve ser excluído primeiro antes que possa ser destruído permanentemente.',
        'email_error' => 'Esse endereço de e-mail pertence a um usuário diferente.',
        'mark_error' => 'Houve um problema ao atualizar este usuário. Por favor, tente novamente.',
        'not_confirmed' => 'Este usuário não está confirmado.',
        'not_found' => 'Esse usuário não existe.',
        'restore_error' => 'Houve um problema ao restaurar este usuário. Por favor, tente novamente.',
        'role_needed' => 'Você deve escolher pelo menos uma função.',
        'role_needed_create' => 'Você deve escolher pelo menos uma função.',
        'session_wrong_driver' => 'Seu driver de sessão deve ser definido como banco de dados para usar esse recurso.',
        'social_delete_error' => 'Houve um problema ao remover a conta social do usuário.',
        'update_error' => 'Houve um problema ao atualizar este usuário. Por favor, tente novamente.',
        'update_password_error' => 'Houve um problema ao alterar a senha deste usuário. Por favor, tente novamente.',
      ),
    ),
  ),
  'frontend' => 
  array (
    'auth' => 
    array (
      'confirmation' => 
      array (
        'already_confirmed' => 'Sua conta já está confirmada.',
        'confirm' => 'Confirme sua conta!',
        'created_confirm' => 'Sua conta foi criada com sucesso. Nós lhe enviamos um e-mail para confirmar sua conta.',
        'created_pending' => 'Sua conta foi criada com sucesso e está pendente de aprovação. Um e-mail será enviado quando sua conta for aprovada.',
        'mismatch' => 'Seu código de confirmação não corresponde.',
        'not_found' => 'Esse código de confirmação não existe.',
        'pending' => 'Sua conta está atualmente com aprovação pendente.',
        'resend' => 'Sua conta não está confirmada. Clique no link de confirmação em seu e-mail ou <a href=":url">clique aqui</a> para reenviar o e-mail de confirmação.',
        'resent' => 'Um novo e-mail de confirmação foi enviado para o endereço registrado.',
        'success' => 'Sua conta foi confirmada com sucesso!',
      ),
      'deactivated' => 'Sua conta foi desativada.',
      'email_taken' => 'Esse endereço de e-mail já está sendo usado.',
      'password' => 
      array (
        'change_mismatch' => 'Essa não é sua senha antiga.',
        'reset_problem' => 'Houve um problema ao redefinir sua senha. reenvie o e-mail de redefinição de senha.',
      ),
      'registration_disabled' => 'O registro está fechado no momento.',
    ),
  ),
);
