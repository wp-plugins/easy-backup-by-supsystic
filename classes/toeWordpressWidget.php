<?php
abstract class toeWordpressWidgetEbbs extends WP_Widget {
	public function preWidget($args, $instance) {
		if(frameEbbs::_()->isTplEditor())
			echo $args['before_widget'];
	}
	public function postWidget($args, $instance) {
		if(frameEbbs::_()->isTplEditor())
			echo $args['after_widget'];
	}
}
