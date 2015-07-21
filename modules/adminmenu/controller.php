<?php
class adminmenuControllerEbbs extends controllerEbbs {
    public function sendMailToDevelopers() {
        $res = new responseEbbs();
        $data = reqEbbs::get('post');
        $fields = array(
            'name' => new fieldEbbsEbbs('name', __('Your name field is required.', EBBS_LANG_CODE), '', '', 'Your name', 0, array(), 'notEmpty'),
            'website' => new fieldEbbsEbbs('website', __('Your website field is required.', EBBS_LANG_CODE), '', '', 'Your website', 0, array(), 'notEmpty'),
            'email' => new fieldEbbsEbbs('email', __('Your e-mail field is required.', EBBS_LANG_CODE), '', '', 'Your e-mail', 0, array(), 'notEmpty, email'),
            'subject' => new fieldEbbsEbbs('subject', __('Subject field is required.', EBBS_LANG_CODE), '', '', 'Subject', 0, array(), 'notEmpty'),
            'category' => new fieldEbbsEbbs('category', __('You must select a valid category.', EBBS_LANG_CODE), '', '', 'Category', 0, array(), 'notEmpty'),
            'message' => new fieldEbbsEbbs('message', __('Message field is required.', EBBS_LANG_CODE), '', '', 'Message', 0, array(), 'notEmpty'),
        );
        foreach($fields as $f) {
            $f->setValue($data[$f->name]);
            $errors = validatorEbbs::validate($f);
            if(!empty($errors)) {
                $res->addError($errors);
            }
        }
        if(!$res->error) {
            $msg = 'Message from: '. get_bloginfo('name').', Host: '. $_SERVER['HTTP_HOST']. '<br />';
            foreach($fields as $f) {
                $msg .= '<b>'. $f->label. '</b>: '. nl2br($f->value). '<br />';
            }
			$headers[] = 'From: '. $fields['name']->value. ' <'. $fields['email']->value. '>';
            wp_mail('support@supsystic.team.zendesk.com', 'Supsystic Ecommerce Contact Dev', $msg, $headers);
            $res->addMessage(__('Done', EBBS_LANG_CODE));
        }
        $res->ajaxExec();
    }
	public function getPermissions() {
		return array(
			EBBS_USERLEVELS => array(
				EBBS_ADMIN => array('sendMailToDevelopers')
			),
		);
	}
}

