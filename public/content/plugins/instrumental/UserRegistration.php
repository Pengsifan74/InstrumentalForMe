<?php

namespace Instrumental;

use WP_User;

class UserRegistration
{
    public function __construct()
    {
      
        // add_action( //Charger un css custom sur nos pages login & register
        //     'login_enqueue_scripts',
        //     [$this, 'loadAssets']
        // );

        add_action( // Inserrer du code Html dans le formulaire de WP afin de le personnaliser
            'register_form',
            [$this, 'addCustomFields']
        );

        add_action( // Gestion des erreurs une fois le formulaire soumis
            'registration_errors',
            [$this, 'checkErrors']
        );

        /* ======================================
           Etape après que l'utilisateur est crée
           ======================================*/

        add_action( // Affectation du rôle de l'utilisateur 
            'registeur_new_user',
            [$this, 'setUserRole']
        );

        add_action( //  Création de la page profil
            'registeur_new_user',
            [$this, 'createUserProfile']
        );

        add_action( // Affectation du MdP choisit par l'utilisateur
            'register_new_user',
            [$this, 'setUserPassword']
        );
    }

            /*====================
                    Méthodes
              ==================== */
     
    public function setUserRole($newUserId)
    {
        $user = new WP_User($newUserId);
        $role = filter_input(INPUT_POST, 'user_type'); // Controle des données enregistrer par l'utilisateur (si rôle non autorisé = suppression de compte et blocage de la page)

        $allowedRoles = [
            'teacher',
            'student'
        ];
        if(!in_array($role, $allowedRoles)) {

            require_once ABSPATH . '/wp-admin/includes/user.php';
            wp_delete_user($newUserId);
            exit('SOMETHING WRONG HAPPENED');
        }
        else {
            $user->add_role($role);
            $user->remove_role('subscriber');
        }
    }

    public function createUserProfile($newUserId)
    {
        $user = new WP_User($newUserId);
        $role = filter_input(INPUT_POST, 'user_type');

        if($role === 'teacher') {
            $postType = 'profile-teacher';
        }
        elseif($role === 'student') {
            $postType = 'profile-student';
        }
         wp_insert_post([
             'post_author' => $newUserId,
             'post_title'  => $user->data->display_name ." 's profile",
             'post-type'   => $postType
         ]);

    }

    public function setUserPassword($newUserId)
    {
        $password = filter_input(INPUT_POST,'user_password');
        wp_set_password($password, $newUserId);
    }

    /*===============================
           Cotrôle du formulaire
      =============================== */

    public function chekErrors($errors)
    {
        $password0 = filter_input(INPUT_POST, 'user_password');
        $password1 = filter_input(INPUT_POST, 'user_password_confirmation');
        $role = filter_input(INPUT_POST,'user_type');
        $allowedRoles = [
            'teacher',
            'student'
        ];
        if(!in_array($role,$allowedRoles)) {
            $errors->add(
                'role-different',
                '<strong>' . __('error: ') .'</strong> Rôle invalide'
            );
        }
        if($password0 !== $password1) {
            $errors->add(
                'passwords-different',
                '<strong>' . __('error: ') .'</strong> Le deuxième mot de passe doit correspondre au premier'
            );
        }
        if(mb_strlen($password0) < 8) {
            $errors->add(
                'password-too-short',
                '<strong>' . __('Error: ') . '</strong> Votre mot de passe doit contenir huit caractères '
            );
        }
        if(!preg_match('/[A-Z]/', $password0)) {
            $errors->add(
                'password-no-capitalized-letter',
                '<strong>' . __('Error: ') . '</strong> Votre mot de passe doit contenir un lettre majuscule '
            );
        }
        if(!preg_match('/[a-z]/', $password0)) {
            $errors->add(
                'password-no-lowercase-letter',
                '<strong>' . __('Error: ') . '</strong> Votre mot de passe doit contenir un lettre minuscule '
            );
        }

        if(!preg_match('/[0-9]/', $password0)) {
            $errors->add(
                'password-no-number',
                '<strong>' . __('Error: ') . '</strong> Votre mot de passe doit contenir un chiffre '
            );
        }


        if(!preg_match('/\W/', $password0)) {
            $errors->add(
                'password-no-special-character',
                '<strong>' . __('Error: ') . '</strong> Votre mot de passe doit contenir un caractère special '
            );
        }
        return $errors;
    }

    /*===============================
        Customisation du formulaire
      =============================== */


    //   public function loadAssets()
    //   {
        
    //       wp_enqueue_style(
    //           'login-form-css',
    //           get_theme_file_uri('assets/css/user-registration.css')
    //       );
    //   } 

     public function addCustomFields() 
     {
        
         echo '
            <p>
                <label for="user_password">Mot de passe</label>
                <input type="text" name="user_password" id="user_password" class="input" value="" size="20" autocapitalize="off">
            </p>

            <p>
                <label for="user_password_confirmation">Confirmer votre mot de passe</label>
                <input type="text" name="user_password_confirmation" id="user_password_confirmation" class="input" value="" size="20" autocapitalize="off">
            </p>

            <p>
                <label for="user_password_confirmation">Je m\'inscrit en tant que </label>
                <select id="user_type" name="user_type">
                    <option value="teacher">Professeur</option>
                    <option value="student">Elève</option>
            </select>
            </p>';

                if('teacher' === 'true' ){
                   echo'
                    <p>
                        <label for="certificate">Je selectionne mon diplôme</label>
                        <select id="certificate" name="certificate">
                            <option value="bts">bts</option>
                            <option value="prepa">prepa</option>
                        </select>
                    </p>';
                }
                
 

         
     }
}
