<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\SignalGenerator;


/**
 * Test secondary legacy panels
 */
class PanelSecodaryLegacyTest extends ILIAS_UI_TestBase {

	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function legacyPanel($title, $content){
				return new I\Component\Panel\Secondary\Legacy($title, $content);
			}
			public function legacy($content) {
				return new I\Component\Legacy\Legacy($content);
			}
			public function dropdown() {
				return new I\Component\Dropdown\Factory();
			}
			public function shy($label, $url) {
				return new I\Component\Button\Shy($label, $url);
			}
			public function viewControl() {
				return new I\Component\ViewControl\Factory(new SignalGenerator());
			}
			public function button() {
				return new I\Component\Button\Factory();
			}
		};
		return $factory;
	}

	protected function cleanHTML($html) {
		$html = str_replace(["\n", "\t"], "", $html);

		return trim($html);
	}

	public function test_implements_factory_interface() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$secondary_panel = $this->getUIFactory()->legacyPanel("List Title", $legacy);

		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Panel\\Secondary\\Legacy", $secondary_panel);
	}

	public function test_get_title() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$secondary_panel = $this->getUIFactory()->legacyPanel("Title", $legacy);

		$this->assertEquals($secondary_panel->getTitle(), "Title");
	}

	public function test_get_legacy_component() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy);

		$this->assertEquals($secondary_panel->getLegacyComponent(), $legacy);
	}

	public function test_with_actions() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$actions = $this->getUIFactory()->dropdown()->standard(array(
			$this->getUIFactory()->shy("ILIAS", "https://www.ilias.de"),
			$this->getUIFactory()->shy("Github", "https://www.github.com")
		));

		$secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
			->withActions($actions);

		$this->assertEquals($secondary_panel->getActions(), $actions);
	}

	public function test_without_viewcontrols() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy);
		$array_vc = $secondary_panel->getViewControls();

		$this->assertEquals($array_vc, null);
	}

	public function test_with_sortation_viewcontrol() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$sort_options = array(
			'internal_rating' => 'Best',
			'date_desc' => 'Most Recent',
			'date_asc' => 'Oldest',
		);
		$sortation = $this->getUIFactory()->viewControl()->sortation($sort_options);

		$secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
			->withViewControls([$sortation]);

		$array_vc = $secondary_panel->getViewControls();

		$this->assertEquals($array_vc[0], $sortation);
	}

	public function test_with_pagination_viewcontrol() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$pagination = $this->getUIFactory()->viewControl()->pagination()
			->withTargetURL("http://ilias.de", 'page')
			->withTotalEntries(98)
			->withPageSize(10)
			->withCurrentPage(1);

		$secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
			->withViewControls([$pagination]);

		$array_vc = $secondary_panel->getViewControls();

		$this->assertEquals($array_vc[0], $pagination);
	}

	public function test_with_section_viewcontrol() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$back = $this->getUIFactory()->button()->standard("previous", "http://www.ilias.de");
		$next = $this->getUIFactory()->button()->standard("next", "http://www.github.com");
		$current = $this->getUIFactory()->button()->standard("current", "");
		$section = $this->getUIFactory()->viewControl()->section($back,$current,$next);

		$secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
			->withViewControls([$section]);

		$array_vc = $secondary_panel->getViewControls();

		$this->assertEquals($array_vc[0], $section);
	}

	//RENDER

	public function test_render_with_actions() {
		$legacy = $this->getUIFactory()->legacy("Legacy content");
		$actions = $this->getUIFactory()->dropdown()->standard(
			$this->getUIFactory()->button()->shy("ILIAS", "https://www.ilias.de"),
			$this->getUIFactory()->button()->shy("GitHub", "https://www.github.com")
		);

		$sec = $this->getUIFactory()->legacyPanel("Title",$legacy)->withActions($actions);

		$html = $this->getDefaultRenderer()->render($sec);

		$expected_html = <<<EOT
<div class="panel panel-secondary">
	<div class="panel-heading ilHeader clearfix">
		<h3 class="ilHeader panel-secondary-title">Title</h3>
		<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
			<ul class="dropdown-menu">
				<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
				<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
			</ul>
		</div>
	</div>
	<div class="panel-body">
		Legacy content
	</div>
</div>
EOT;
		$this->assertHTMLEquals(
			$this->cleanHTML($expected_html),
			$this->cleanHTML($html)
		);
	}
	/*
		public function test_render_with_sortation() {
			$legacy = $this->getUIFactory()->legacy("Legacy content");
			$sort_options = array(
				'internal_rating' => 'Best',
				'date_desc' => 'Most Recent',
				'date_asc' => 'Oldest',
			);
			$sortation = $this->getUIFactory()->viewControl()->sortation($sort_options);
			$sec = $this->getUIFactory()->legacyPanel("Title",$legacy)
				->withViewControls([$sortation]);

			$html = $this->getDefaultRenderer()->render($sec);

			$expected_html = <<<EOT
	<div class="panel panel-secondary">
		<div class="panel-heading ilHeader clearfix">
			<h3 class="ilHeader panel-secondary-title">Title</h3>
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false"> <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
					<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button></li>
				</ul>
			</div>
		</div>
		<div class="panel-body">
			Legacy content
		</div>
	</div>
	EOT;
			$this->assertHTMLEquals(
				$this->cleanHTML($expected_html),
				$this->cleanHTML($html)
			);
		}
	*/
}
