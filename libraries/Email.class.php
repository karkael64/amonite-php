<?php

if( !class_exists( "Email" ) ) {

    class Email {

        const ADMIN_EMAIL =       "webmaster@sem-rscc.org.fr";
        const ADMIN_EMAIL_NAME =  "SEM RSCC Webmaster";
        const ADMIN_ERROR_TITLE = "Admin Error Report";
        const SITE_NAME =         "SEM RSCC";

        private $from =    null;
        private $to =      null;
        private $cc =      [];
        private $bcc =     [];
        private $replyTo = null;

        private $subject = "";
        private $body =    "";
        private $files =   [];

        private $isForceUtf8 = false;

        const MIME_VERSION =   "1.0";

        /**
         *  @functions setForm, setTo, setReplyTo, addCC, addBCC, addFile, addFiles, setSubject, setBody, setBodyFromTemplate
         *  @param string $addr
         *  @param string|null $name
         *  @return Email
         */

        public function setFrom( $addr, $name = null ) {    $this->from =    [ $addr => $name ]; if( !$this->replyTo ) $this->setReplyTo( $addr, $name ); return $this; }
        public function setTo( $addr, $name = null ) {      $this->to =      [ $addr => $name ];                                                          return $this; }
        public function setReplyTo( $addr, $name = null ) { $this->replyTo = [ $addr => $name ];                                                          return $this; }
        public function addCC( $addr, $name = null ) {
            if( is_array( $addr ) ) foreach( $addr as $k => $a ) $this->addCC( $k, $a );
            else $this->cc[ $addr ] = $name;
            return $this;
        }
        public function addBCC( $addr, $name = null ) {
            if( is_array( $addr ) ) foreach( $addr as $k => $a ) $this->addBCC( $k, $a );
            else $this->bcc[ $addr ] = $name;
            return $this;
        }
        public function addFile( $file ) {       $this->files[] = $file;                                                      return $this; }
        public function addFiles( $files ) {     if( is_array( $files ) ) foreach( $files as $file ) $this->addFile( $file ); return $this; }
        public function setSubject( $subject ) { $this->subject = $subject;                                                   return $this; }
        public function setBody( $body ) {       $this->body =    $body;                                                      return $this; }
        public function setBodyFromTemplate( $template, $args ) {
            if( file_exists( $template ) && !is_dir( $template )) {
                unset( $args[ "template" ] );
                extract( get_object_vars( $this ) );
                extract( $args );
                ob_start();
                require_once $template;
                ( $data = ob_get_clean() ) and $this->setBody( $data );
            }
            return $this;
        }



        //  ----------------------------------------------------------------
        //  @return string

        private function getFrom() {    return ( ( $t = $this->format_list_nom_email( $this->from ) ) ?    "FROM: "        . $t . "\r\n" : null ); }
        private function getTo() {      return ( ( $t = $this->format_list_nom_email( $this->to ) ) ?      "TO: "          . $t . "\r\n" : null ); }
        private function getCC() {      return ( ( $t = $this->format_list_nom_email( $this->cc ) ) ?      "CC: "          . $t . "\r\n" : null ); }
        private function getBCC() {     return ( ( $t = $this->format_list_nom_email( $this->bcc ) ) ?     "BCC: "         . $t . "\r\n" : null ); }
        private function getReplyTo() { return ( ( $t = $this->format_list_nom_email( $this->replyTo ) ) ? "RETURN-PATH: " . $t . "\r\nREPLY-TO: " . $t . "\r\n" : null ); }
        private function getSubject() { return ( "=?UTF-8?B?" . base64_encode( $this->isForceUtf8 ? utf8_encode( $this->subject ) : $this->subject ) . "?=" ); }

        public function forceUtf8(){
            $this->isForceUtf8 = true;
        }



        //  ----------------------------------------------------------------
        //  @return string

        private function format_nom_email( $addr, $name = null ) {
            if( $name && strlen( $name ) ) {
                $this->isForceUtf8 and ( $name = utf8_encode( $name ) );
                return "\"$name\" <$addr>";
            }
            else return "<$addr>";
        }
        private function format_list_nom_email( $list ) {
            $res = [];
            foreach( $list as $addr => $name )
                $res[] = $this->format_nom_email( $addr, $name );
            return implode( ', ', $res );
        }



        //  ----------------------------------------------------------------
        //  @return string

        private function buildHeader( $bound_email ) {
            return
                $this->getFrom() .
                $this->getTo() .
                $this->getCC() .
                $this->getBCC() .
                $this->getReplyTo() .
                "MIME-VERSION: " . self::MIME_VERSION . "\r\n" .
                "CONTENT-TYPE: multipart/mixed; boundary=\"".$bound_email."\"\n\n";
        }



        //  ----------------------------------------------------------------
        //  @return string

        private function buildBodyHTML() {

            $t =
                "Content-Type: text/html; charset=\"UTF-8\"
Content-Transfer-Encoding: 8bit
".$this->body."
";

            return $this->isForceUtf8 ? utf8_encode( $t ) : $t;
        }



        //  ----------------------------------------------------------------
        //  @return string

        private function buildBodyPlain() {

            $t =
                "Content-Type: text/plain; charset=\"UTF-8\"\n" .
                "Content-Transfer-Encoding: 8bit\n\n" .
                strip_tags( $this->body ) . "\r\n\n";
            $this->isForceUtf8 and ( $t = utf8_encode( $t ) );
            return $t;
        }



        //  ----------------------------------------------------------------
        //  @return string

        private function buildFiles( $bound_email ) {
            $str = "";
            foreach( $this->files as $file ) {

                if( is_array( $file ) && $file['error'] == 0 ) {
                    $file_type = $file['type'];
                    $file_name = basename( $file['name'] );
                    $str .=
                        "--$bound_email
Content-Type: $file_type; name=\"$file_name\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename=\"$file_name\"
".chunk_split( base64_encode( @file_get_contents( $file['tmp_name'] ) ) )."\r\n\n";
                }
                else {
                    $file_type = filetype( $file );
                    $file_name = basename( $file );
                    $str .=
                        "--$bound_email
Content-Type: $file_type; name=\"$file_name\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename=\"$file_name\"
".chunk_split( base64_encode( @file_get_contents( $file ) ) )."\r\n\n";
                }
            }

            return $str;
        }



        //  ----------------------------------------------------------------
        //  @return string

        private function buildBody( $bound_email, $bound_content ) {
            return
                "--".$bound_email."\r\n" .
                "Content-Type: multipart/alternative; boundary=\"$bound_content\"\n\n" .
                "--$bound_content\r\n" .
                $this->buildBodyPlain() .
                "--$bound_content\r\n" .
                $this->buildBodyHTML() .
                "--$bound_content--\r\n" .
                $this->buildFiles( $bound_email ) .
                "--".$bound_email."--\r\n";
        }



        //  ----------------------------------------------------------------
        //  @return boolean / status_code

        public function send() {

            foreach( $this->to as $t => $k )
                $to = $t;

            if( !isset( $to ) or !$to )
                return false;

            $bound_email =   md5(uniqid(mt_rand()));
            $bound_content = md5(uniqid(mt_rand()));

            $bo = mail(
                $to,
                $this->getSubject(),
                $this->buildBody( $bound_email, $bound_content ),
                $this->buildHeader( $bound_email )
            );
            return $bo;
        }

        static function getPictureEmbed( $file ) {

            if( file_exists( $file ) )
                return "data:image/".( pathinfo( $file, PATHINFO_EXTENSION ) ).";charset=utf-8;base64,".( base64_encode( file_get_contents( $file ) ) );
            else
                return null;
        }

        static function instanceOld( $options ) {

            return Email::instance()
                ->setFrom(
                    ( isset($options["nomExpediteur"]) && $options["nomExpediteur"] ? $options["nomExpediteur"] : null ),
                    ( isset($options["expediteur"]) && $options["expediteur"] ?       $options["expediteur"] :    $options["configSite"]["mail"]["expediteur"] )
                )
                ->setTo(      isset($options["destinataire"]) && $options["destinataire"] ? $options["destinataire"] : $options["configSite"]["mail"]["destinataire"] )
                ->setSubject( isset($options["sujet"]) ?                                    $options["sujet"] :        null )
                ->setBody(    isset($options["message"]) ?                                  $options["message"] :      null )
                ->setReplyTo( isset($options["replyTo"]) && $options["replyTo"] ?           $options["replyTo"] :      $options["configSite"]["mail"]["reply"] )
                ->addFiles(   isset($options["fichiers"]) && $options["fichiers"] ?         $options["fichiers"] :     array() );
        }

        static function instanceFromArray( $options ) {

            $e = new Email;
            isset( $options[ "from" ] )    and $e->setFrom(    $options[ "from" ] );
            isset( $options[ "to" ] )      and $e->setTo(      $options[ "to" ] );
            isset( $options[ "cc" ] )      and $e->addCC(      $options[ "cc" ] );
            isset( $options[ "bcc" ] )     and $e->addBCC(     $options[ "bcc" ] );
            isset( $options[ "replyTo" ] ) and $e->setReplyTo( $options[ "replyTo" ] );
            isset( $options[ "subject" ] ) and $e->setSubject( $options[ "subject" ] );
            isset( $options[ "body" ] )    and $e->setBody(    $options[ "body" ] );
            isset( $options[ "file" ] )    and $e->addFile(    $options[ "file" ] );
            isset( $options[ "files" ] )   and $e->addFiles(   $options[ "files" ] );
            if( isset( $options[ "forceUtf8" ] ) and $options[ "forceUtf8" ] )
                $e->forceUtf8();
            return $e;
        }

        static function instance(){

            return new Email;
        }

        static function AdminError( $message = null ) {

            return Email::instance()
                ->setTo( self::ADMIN_EMAIL, self::ADMIN_EMAIL_NAME )
                ->setSubject( self::SITE_NAME . ':' . self::ADMIN_ERROR_TITLE )
                ->setBody( $message )
                ->send();
        }
    }
}
