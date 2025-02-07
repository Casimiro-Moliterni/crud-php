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
     * Array con la lista delle rotte, callback associato , parametru e priorità
     * @var array
     */
    protected $routes = array();
    /**
     * Array contenente la lista delle rotte prima di essere elaborate
     * @var array
     */
    protected $routes_original = array();
    /**
     * stampa a video gli errori della tabella di routing. utilizzato per il debug
     * @var  booleano
     */
    protected $show_errors = false;
    /**
     * funzione di callback delle rotte
     * @var object
     */
    protected $callback = null;
    /**
     * rotta di default
     * @var object
     */
    protected $default_route = null;
    /**
     * array di parametri passati alla callback
     * @var array
     */
    protected $params = array();
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
           // Gestisco il caso di wildcard senza nome esplicito
            $route = preg_replace('/\<\*\>/', '(?P<wildcard>.+)', $route);
            
            // Catturo la rotta alfa numerica (0-9A-ZA-z_), formato: <:var_name>
            $route = preg_replace('/\<\:([^>]+)\>/', '(?P<$1>[A-Za-z0-9\-\_]+)', $route);
            
            // Catturo la rotta nel formato numerico (0-9), formato: <#var_name>
            $route = preg_replace('/\<#([^>]+)\>/', '(?P<$1>[0-9]+)', $route);
            
            // Catturo la rotta nel formato wildcard con nome, formato: <*var_name>
            $route = preg_replace('/\<\*([^>]+)\>/', '(?P<$1>.+)', $route);

             // Aggiunta di una costante di default se BASE_PATH non è definito
             $base_path = defined('BASE_PATH') ? BASE_PATH : '';
            // concludo la costruzione della regex
            $route = '#^'.BASE_PATH.$route.'$#';

            //controllo se la rotta esiste già nella tabella di routing
            if(isset($this->routes[$priority][$route])){
                //se la rotta è già registrata nella tabella di routing lancio un eccezione
                if($this->show_errors){
                    throw new Exception('L\'URI "'.htmlspecialchars($route).'" esiste già nella tabella di routing.');
                }
                return false;
            }
            
            // aggiungo la rotta nella tabella di routing
            $this->routes[$priority][$route] = $callback;
            $this->routes_original[$priority][$route] = $original_route;

     
   }
         
   /**
    * lancia la funzione relativa all'url corrente.
    * @method run
    * @return array
    */
     public function run(){
        //flag per url matchat o no
        $matched_route = false;
        // ordino l'array con le rotte per priorità di esecuzione
        ksort($this->routes);

        // scorro le rotte in ordine di priorità
        foreach($this->routes as $priority => $routes){
           // Per ogni rotta mi ricavo la funzione di callback 
           foreach($routes as $route => $callback){
            // controllo se la rotta metcha l'url corrente
            if(preg_match($route,$this->url_clean,$matches)){
                // trovata una rotta corrispondente all'url 
                $matched_route = true;
                // Lista dei parametri passati alla callback
                $params = array($this->url_clean);
                // mi ricavo il nome dei parametri 
                foreach($matches as $key => $match){
                    if(is_string($key)){
                        $params[] = $match;
                    }
                }
                // imposto i parametri e la funzione di callback
                $this->params = $params;
                $this->callback = $callback;
                // ritorno un vettore con tutti i dati della rotta
                return array('callback'=> $callback,'params'=> $params, 'route' => $route, 'original_route' => $this->routes_original[$priority][$route]);
            }
           }
        }
        // se non è stata metchata nessuna rotta , imposto i valori di default dell'oggetto
        if(!$matched_route && $this->default_route !== null){
            $this->params = $callback;
            $this->callback = $this->default_route;
            $this->routes = false;
            $this->routes_original = false;
        }
     }
   /**
    * chiama la funzione di callback appropriata e passa i parametri indicati forniti da Router->run()
    * @method dispatch
    * @return boolean
    */
     public function dispatch(){
          if($this->callback === null || $this->params == null){
            throw new Exception('nessuna callback o parametri trovati, eseguire $router->run prima di $router->dispatch');
            return false;
          }
          call_user_func_array($this->callback, array($this->params));

          return true;
     }
   /**
    * Esegue il routing match e poi esegue la funziona di callback associata
    * @method execute
    */
     public function execute(){
        $this->run();
        $this->dispatch();
         }
   /**
    * Rotta di default. viene lanciata se non si trovano corrispondenze nell'url (es pagina 404)
    * @method default_route
    *@param object object $callaback
    *@return object
    */
     public function default_route($callback){
          $this->default_route = $callback;
          return $this;
     }
   /**
    * abilita la visualizzazione a video degli errori . per il debug
    * @method show_errors
    *@method show_errors
    *@return object
    */
     public function show_errors(){
       $this->show_errors = true;
       return $this;
     }
   /**
    * Disabilita la visualizzazione a video degli errori per il debug
    * @method hide_error
    *@return object
    */
     public function hide_errors(){
        $this->show_errors = false;
        return $this;
     }

}
?>
