<?php 
/**
 * Classe per la gestione delle rotte
 */
class Router
{   
    /**
     * Url sanitizzato escluso il dominio e la base root
     * @var string
     */
    protected $url_clean = '';
    /**
     * Url "sporco" da elaborare, direttamente letto da $_SERVER['REQUEST_URI']
     * @var string
     */
    protected $url_dirty = '';
    /**
     * Costruttore
     * @method __construct
     * @param string $url
     */
    public function __construct($url = null){
        //mi ricavo l'url corrente
        if($url == null){
            if(!empty($_SERVER['REQUEST_URL'])){
                 $url = $_SERVER['REQUEST_URL'];
            } else {
                $url = $_SERVER['REQUEST_URI'];
            }
        }

        //memorizza l'url corrente da sanitizzare
        $this->url_dirty = $url;
        //pulisco l'url rimuovendo il protocollo, il dominio e la directory base se esiste
        $this->url_clean = $this->_get_clean_url($this->url_dirty);

        echo 'dirty url:'. $this->url_dirty.'<br>';
        echo 'clean url:'. $this->url_clean.'<br>';
    }

   /**
    * Ritorna la parte dell'url dopo la directory di root, escludendo la query string ed aggiungendo uno slash
    *@method _get_clean _url
    *@param string $url
    *@return string
    */
    private function _get_clean_url($url){
     //mi ricavo la posizione dell'inizio della query string
     $query_string = strpos($url,'?');
     //elimino la query string dall'url
     if($query_string !== false){
        $url = substr($url, 0,$query_string);
    }
    //se nell'url c'è /index.php lo rimuovo
     if(substr($url,1,strlen(basename($_SERVER['SCRIPT_NAME']))) === basename( $_SERVER['SCRIPT_NAME'])){
        $url = substr( $url,strlen( basename( $_SERVER['SCRIPT_NAME'])) + 1 );
     }
     //aggiungo alla fine dell'url uno slash /
     $url = rtrim($url,'/').'/';
     //correggo gli slash multipli nell'url /my/dir//url/ --> /my/dir/url
     $url = preg_replace('/\/+/','/',$url);

     return $url;
    }
     /**
      * @method route
      *@param string $route
      *@param object callable $callback
      *@return  boolean
      */
     public function route($route,$callback,$priority = 10){
             //tengo l'url originale per il debug
             $original_route = $route;
             //Mi assicuro che la rotta finisca con lo /
             $route = rtrim($route,'/').'/';
             // Catturo la rotta alfa numerica (0-9A-ZA-z_), formato: <:var_name>
             $route = preg_replace('/\<\:(.*?)\>/','(?P<\1>[A-Za-z0-9\-\_]+)',$route);
             // Catturo la rotta nel formato numeric0 (0-9), formato: <#var_name>
             $route = preg_replace('/\<#(.*?)>/','(?P<\1>[0-9]+)',$route);
            // Catturo la rotta nel formato wildcard, formato: <*var_name>
            $route = preg_replace('/\<*(.*?)>/','(?P<\1>.+)',$route);
            
   }
            

}
?>