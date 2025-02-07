<?php 
// pagina home 
$Router->route('/',function(){
    echo'<h1>HOME</h1>';
});
//pagina chi siamo con parametro wildcard
$Router->route('/chi-siamo/<*>',function($args){
    echo'<h1>Chi siamo</h1>';
});
//pagina dove siamo con parametro numerico
$Router->route('/dove-siamo/<#id>',function($args){
    echo'<h1>Dovesiamo</h1>';
    echo '<pre>';
    var_dump($args);
    echo '</pre>';
});
//pagina blog con parametro stringa
$Router->route('/blog/<:slug>',function($args){
    echo'<h1>Blog</h1>';
});
// pagina error 404 per url non trovato
$Router->default_route(function(){
    echo'<h1>error 404</h1>';
});

$Router->execute();
?>