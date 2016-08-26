<?php 
class Bull {
    public static function init(){
        self::init_hooks();
    }
    public static function init_hooks(){
        add_action( 'rest_api_init', function () {
            register_rest_route( 'apiviewer', '/(?P<prefix>\w+)/(?P<version>\w+)/(?P<post_type>\w+)', array(
                'methods' => 'GET',
                'callback' => array('Bull','format_link_api'),
            ) );
        } );
    }
    public static function format_link_api($data){
        if(isset($data['prefix'])&&isset($data['version'])&&isset($data['post_type']) ){
            $route = get_bloginfo('url').'/wp-json/'.$data['prefix'].'/'.$data['version'].'/'.$data['post_type'];
            $response = wp_remote_get( $route );
            if( is_wp_error( $response ) ) {
                return null;
            }
            $body = wp_remote_retrieve_body( $response );
            self::format_square_brackets($body);
            self::format_brackets($body);
            self::format_commas($body);
            echo "</pre>";
            return $body;
        }
        return null;    
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
        if($innerpos<$outerpos&&$innerpos>0&&$outerpos>0){
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
        if($innerpos<$outerpos&&$innerpos>0&&$outerpos>0){
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
}
