<?php

if( !class_exists( "NavigationComponent" ) ) {

	require_once ROOT . "/datas/models/NavModel.php";

	class NavigationComponent extends Component {

		function onCall( Request $req, Response $res ) {

			$n = new NavModel();
			if( !$n->count() ) {
				$n->insert( array( "url" => "#a", "name" => "A" ) );
				$n->insert( array( "url" => "#b", "name" => "B" ) );
				$n->insert( array( "url" => "#c", "name" => "C" ) );
			}

            $h = new HttpCode( 200, array( "success" => true, "count" => $n->count() ) );
			$h->setMime( "application/json" );
            throw $h;
		}

		function getComponent( Request $req, Response $res ) {

			$n = new NavModel();
            $rows = $n->select();

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

