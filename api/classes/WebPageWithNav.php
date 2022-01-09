<?php

class WebPageWithNav extends BootstrapWebPage {

	private $nav;

	/**
  	* Override the constructor
  	*/
	function __construct($pageTitle, $pageHeading1, $navItems, $footerText) {
		$this->pageStart = $this->makePageStart($pageTitle);
		$this->header = $this->makeHeader($pageHeading1);
		$this->nav = $this->makeNav($navItems);
		$this->main = "";
		$this->footer = $this->makeFooter($footerText);
		$this->pageEnd = $this->makePageEnd();

	}

	/**
  	* @return string The Bootstrap nav.
  	*
  	* @param array $navItems The menu items for the nav
  	*/
	private function makeNav(array $navItems) {

		$mynav = <<< MYNAV1
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Navbar</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
MYNAV1;

		foreach ($navItems as $key => $value) {
			$mynav .= "<li class=\"nav-item active\">";
			$mynav .= "<a class=\"nav-link\" href=\"$value\">$key</a></li>";
			

		}
		$mynav .= <<< MYNAV2
    </ul>
  </div>
</nav>
MYNAV2;

		return $mynav;
	}

	/**
  	* Override the parents method
  	*/
	public function getPage() {

		$this->main = $this->makeMain($this->main);

		return 	$this->pageStart.
				$this->header.
				$this->nav.
				$this->main.
				$this->footer.
				$this->pageEnd; 
	}

}

?>
