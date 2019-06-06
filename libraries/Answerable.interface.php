<?php

namespace Amonite;

interface Answerable {

	// HEADER
	public function setHeader( $field, $value );
	public function removeHeader( $field );

	public function addCookie( $field, $value, $expires = NULL, $domain = NULL, $path = NULL, $secure = false, $httpOnly = false );
	public function removeCookie( $field );

	public function setMime( $mime );
	public function setCharset( $charset );

	// BODY
	public function getContent();
	public function __toString();
}
