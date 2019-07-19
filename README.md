# Amonite
Amonite is a web client-side ([JS](https://github.com/karkael64/amonite-front)) and server-side (PHP and [NodeJS](https://github.com/karkael64/amonite-js)) engine. It is an engine that complies with HTTP standards, lightweight, easy to use. It's an engine that lets you decide how the program should go forward.

## Amonite-PHP
Amonite is a good PHP engine :
- with NoSQL or PDO,
- with torque Request / Controller,
- with Document / Component couple and
- with Throwable / Answerable response easy to use.

### Why would you use Amonite-PHP
1. Easy paradigm,
2. Soft framework,
3. Easy [installation](installation.md),
4. 3 Steps document sending and
5. Follow HTTP Standards.

### Why would you NOT use Amonite-PHP
1. You need heavy control system,
3. You don't trust your teammates and
2. Expensive tools are most qualitative.

## Start with Amonite-PHP
Entire Amonite engine is archived as a PHAR file. Put it on root, or any where : `/amonite.phar`

May be you would like use it with default config. Then just call it like :
``` PHP
<?php

// Get library
require_once __DIR__ . "/amonite.phar";

// Set file root
Amonite\Response::i()->env->theme = __DIR__ . "/theme/main";

// Execute anonymous function then send response
Amonite\Response::send(Amonite\Controller::auto());
```

## Enhance Amonite-PHP
Amonite default config uses Throwable, Answerable, Request, Response and CustomException. These classes helps you to produce better system in CraftmanShip approach. `Amonite\Response::send($value)` can send scalar values (integer, float, string or boolean) or array or any `Answerable` child class (`CustomException`, `Content`, `Document`, `Component`, `File` or `DownloadFile` Amonite classes available by default).

### Answerable file : Document / Component model
You can enhance the engine with Document / Component model or you can create any class that extends `Content` class or implements `Answerable` interface. In example :

__File : /templates/documents/IndexDocument.php__
``` PHP
<?php
require_once ROOT . "/templates/components/NavComponent.php";
namespace App;

class IndexDocument extends Amonite\Document {
  function getDocument( Amonite\Request $req, Amonite\Response $res ) {

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
``` PHP
<?php
namespace App;

class NavComponent extends Amonite\Component {

  // called only if an argument key (into $argv, $_FILES, $_GET, $_POST) is "Nav"
  function onCall( Request $req, Response $res ) {
    throw new HttpCode( 403 ); // Forbidden access
  }

  // function called by $this->getContent()
  function getComponent( Amonite\Request $req, Amonite\Response $res ) {
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
``` PHP
<?php
namespace App;
return new IndexDocument;

```

### Database management : ModelBSON / ModelPDO model

You can enhance the engine with ModelBSON / ModelPDO model or you can create any class that implements `Model` interface. Use one of these Model, then  In example :

``` PHP
<?php
namespace Amonite;

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

[Documentation & References](how_it_works.md)
