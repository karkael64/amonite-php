# Amonite 
 Amonite is a web client-side ([JS](#not-yet)) and server-side (PHP and [NodeJS](#not-yet)) engine. It is an engine that complies with HTTP standards, lightweight, easy to use. It's an engine that lets you decide how the program should go forward.

## Amonite-PHP
 Amonite is a good PHP engine :
 - with NoSQL or PDO, 
 - with torque Request / Controller, 
 - with Document / Component couple and 
 - with Throwable / Answerable response easy to use.

### Why would you use Amonite-PHP
 1. Easy paradigm, 
 2. Soft framework, 
 3. Easy installation,
 4. 3 Steps document sending and
 5. Follow HTTP Standards.

### Why would you NOT use Amonite-PHP
 1. You need heavy control system, 
 3. You don't trust your colleagues and
 2. Expensive tool are most qualitative.

## Start with Amonite-PHP
 All Amonite engine is archived as a PHAR file. Put it on root, or any where :
 `/amonite.phar`
 
 May be you would like use it with default config. Then just call it like : 
 ```
 <?php
 
 // Get library
 require_once __DIR__ . "/amonite.phar";
 
 // Execute anonymous function then send response
 Response::send( function(){
     
     // Set file root
     Response::i()->env->theme = __DIR__ . "/theme/main";
     
     return Controller::auto();
 });
 ```
 
 [What does default config do ?](how_it_works.md#amonite-php-particularly)

## Enhance Amonite-PHP
 Amonite default config uses Throwable, Answerable, Request, Response and CustomException. 
 
### Answerable file : Document / Component model
 You can enhance the engine with Document / Component model or you can create any class that extends `Content` class or implements `Answerable` interface. In example :
 
__File : /templates/documents/IndexDocument.php__
 ```
 <?php
 
 Request::i()->env->components = ROOT . "/templates/components";
 Request::getComponent( "Nav" );
 // OR
 // require_once ROOT . "/templates/components/NavComponent.php";
 
 class IndexDocument extends Document {
     function getDocument( Request $req, Response $res ) {
     
         ?>
<!doctype html>
<html>
    <head>
    </head>
    <body>
        <?php echo ( new NavComponent() )->getContent(); ?>
    </body>
</html><?php
     }
 }
 ```
 
__File : /templates/components/NavComponent.php__
 ```
 <?php
 
 class NavComponent extends Component {
 
     // called only if an argument (into $argv, $_FILES, $_GET, $_POST) equals "Nav"
     function onCall( Request $req, Response $res ) {
     
         throw new HttpCode( 403 ); // Forbidden access
     }
 
     // function called by $this->get() or $this->getContent()
     function getComponent( Request $req, Response $res ) {
         
         ?>
         <nav>
             <ul>
                 <li>
                     <a href="#a">A</a>
                 </li>
                 <li>
                     <a href="#b">B</a>
                 </li>
                 <li>
                     <a href="#c">C</a>
                 </li>
             </ul>
         </nav>
         <?php
     }
 }
 ```

__File : /theme/main/index.html.php__
 ```
 <?php
 
 Request::i()->env->documents = ROOT . "/theme/main";
 Request::getDocument( "Index" );
 // OR
 // require_once ROOT . "/templates/documents/IndexDocument.php";
 
 return new IndexDocument;

 ```
 
### Database management : ModelBSON / ModelPDO model
 You can enhance the engine with ModelBSON / ModelPDO model or you can create any class that implements `Model` interface. Use one of these Model, then  In example :
 ```
 interface Model {
 
     public function __construct( $name = "" );
 
     public function select( $fields = array(), $where = array(), $limit = 0, $start_at = 0 ); // return array of items
     public function selectFirst( $fields = array(), $where = array(), $start_at = 0 ); // return first items
     public function count( $where = array(), $limit = 0, $start_at = 0 ); // return integer count
     public function remove( $where = array(), $limit = 0, $start_at = 0 ); // return array of items
     public function update( $value = array(), $where = array(), $limit = 0, $start_at = 0 ); // return array of items items
     public function insert( $value = array() ); // return item
 }
 ```

## Documentation & References

[Documentation & References](#)

