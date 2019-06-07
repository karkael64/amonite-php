<?php

if( !class_exists( "NavigationComponent" ) ) {

	require_once ROOT . "/datas/models/NavModel.php";

	class NavigationComponent extends Amonite\Component {

		function onCall( Amonite\Request $req, Amonite\Response $res ) {

			if( !NavModel::count() ) {
				NavModel::insert( array( "url" => "#a", "name" => "A" ) );
				NavModel::insert( array( "url" => "#b", "name" => "B" ) );
				NavModel::insert( array( "url" => "#c", "name" => "C" ) );
			}

      $h = new Amonite\HttpCode( 200, array( "success" => true, "count" => NavModel::count() ) );
			$h->setMime( "application/json" );
    	throw $h;
		}

		function getComponent( Amonite\Request $req, Amonite\Response $res ) {

			NavModel::insert( array("url" => "#/aze", "name" => "AZE!") );
      $rows = NavModel::select();

			?>

            <ul>
                <?php foreach( $rows as $nav ): ?><li>
                    <a href="<?php echo $nav[ "url" ]; ?>"><?php echo $nav[ "name" ]; ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php
		}
	}
}
