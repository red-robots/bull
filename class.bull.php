<?php
class Bull {
	private static $initiated = false;
	public static $route = "";
	
	public static function init() {		
		$current_user = wp_get_current_user();
		//only run plugin as admin editor and not on admin page
		if ( user_can( $current_user, "edit_posts" ) && !is_admin() && ! self::$initiated ) {
			self::init_hooks();
		}
	}
	
	private static function init_hooks(){
		self::$initiated = true;
		$uri = $_SERVER['REQUEST_URI'];
		$matches = array();
        if(preg_match('/.*?apiviewer(.*)/i',$uri,$matches)===1){
            if(count($matches)===2){
                self::$route = $matches[1];
            }
            echo '<link href="'.plugin_dir_url( __FILE__ ).'css/bull.css" type="text/css" rel="stylesheet">';
            self::buffer_output();
            add_action( 'after_theme_setup', array('Bull','flush_buffer') );
		}
    }
    public static function buffer_output(){
        ob_start(array('Bull','format_link_api')); 
    }
    public static function format_link_api($buffer){
        $response = wp_remote_get( get_bloginfo('url').'/wp-json'.self::$route );
        if( is_wp_error( $response ) ) {
            return $buffer;
        }
        $body = wp_remote_retrieve_body( $response );
        $body = json_decode($body);
        return self::dump($body);
    }
    public static function flush_buffer(){
        ob_end_flush(); 
    }
    public static function dump($body){
        $newbody = "";
        if(is_array($body)){
            $newbody.='[<div class="tab">';
        }
        else if(is_object($body)){
            $newbody.='{<div class="tab">';
        }
        foreach($body as $key=>$value){
            if(is_array($value)){
                $newbody.='"'.$key.'":'.self::dump($value);
            }
            else if(is_object($value)){
                $newbody.='"'.$key.'":'.self::dump($value);
            }
            else {
                if(preg_match('/http/',$value)===1){
                    $link_value = str_replace("wp-json","apiviewer",$value);
                    $newbody.='"'.$key.'":"<a href="'.$link_value.'">'.htmlentities($value).'</a>",<br>';
                } else {
                    $newbody.='"'.$key.'":"'.htmlentities($value).'",<br>';
                }
            }
        }
        if(is_array($body)){
            $newbody.='</div>]<br>';
        }
        else if(is_object($body)){
            $newbody.='</div>}<br>';
        }
        return $newbody;
    }
    public static function format_commas(&$body){
        $body = preg_replace('/\,/',',<br>',$body);
    }
    public static function format_brackets(&$body, $offset = 0){
        if($offset<0||$offset>strlen($body)){
            return;
        }
        $innerpos = strpos($body,'{',$offset);
        $outerpos = strpos($body,'}',$offset);
        if($outerpos===false){
            return;
        }
        if($innerpos<$outerpos&&$innerpos>=0&&$outerpos>=0){
            $start = substr($body,0,$innerpos+1);
            $end = substr($body, $innerpos+1);
            $body = $start.'<div class="tab">'.$end;
            self::format_brackets($body,$innerpos+1);
        } else {
            $start = substr($body,0,$outerpos);
            $end = substr($body, $outerpos);
            $body = $start.'</div>'.$end;
            self::format_brackets($body,$outerpos+7);
        }
    }
    public static function format_square_brackets(&$body, $offset = 0){
        if($offset<0||$offset>strlen($body)){
            return;
        }
        $innerpos = strpos($body,'[',$offset);
        $outerpos = strpos($body,']',$offset);
        if($outerpos===false){
            return;
        }
        if($innerpos<$outerpos&&$innerpos>=0&&$outerpos>=0){
            $start = substr($body,0,$innerpos+1);
            $end = substr($body, $innerpos+1);
            $body = $start.'<div class="tab">'.$end;
            self::format_square_brackets($body,$innerpos+1);
        } else {
            $start = substr($body,0,$outerpos);
            $end = substr($body, $outerpos);
            $body = $start.'</div>'.$end;
            self::format_square_brackets($body,$outerpos+7);
        }
    }
}
