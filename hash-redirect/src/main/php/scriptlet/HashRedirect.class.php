<?php namespace scriptlet;

/**
 * Redirects URLs of the form `/base/-/resource` to `/base/#/resource`.
 * Can be useful when pushing hash-URLs through redirects
 */
class HashRedirect extends HttpScriptlet {
  const TEMPLATE= '
    <html>
      <head><title>Redirecting...</title></head>
      <body>
        <script language="JavaScript">
          document.location.href= "%1$s";
        </script>
        <noscript>
          <a href="%1$s">Continue</a>
        </noscript>
      </body>
    </html>
  ';

  /**
   * Handles GET request
   *
   * @param  scriptlet.Request $request
   * @param  scriptlet.Response $response
   */
  public function doGet($request, $response) {
    $uri= $request->getURL();
    $uri->setPath(strtr($uri->getPath(), array('/-' => '/#')));

    $response->write(sprintf(strtr(self::TEMPLATE, array("\n    " => "\n")), $uri->getURL()));
  }
}