<?php


if( !class_exists( "IndexDocument" ) ) {

  require_once ROOT . "/templates/components/NavigationComponent.php";
  require_once ROOT . "/templates/components/UnitComponent.php";

  class IndexDocument extends Amonite\Document {

    function getDocument( Amonite\Request $req, Amonite\Response $res ) {

      //$this->setMime( "text/plain" );

      ?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="/unit.css">
</head>
<body>
    <h1>
         Hello World!
        <small>You are in <?php echo $sub = ( $sub = Amonite\Request::getSubWebsiteName() ) ? ucwords( $sub ) : "Main"; ?>&hellip;
        </small>
    </h1>
    <nav>
        <?php echo ( new NavigationComponent() )->getContent(); ?>

    </nav>
    <main>
		<?php echo ( new UnitComponent() )->getContent(); ?>

    </main>
</body>
</html><?php
    }
  }
}
