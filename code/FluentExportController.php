<?php
// todo:
// className multiple times -> block
// show fieldnames as table-header
// group output by classes
// CSV-Export and complete (all tables) archive.tar.gz
// does this FluentContentController extension make sense
	// rename config file
// have Table & Lang-Nav Bootstrap styled
// stripe-out has_ons -> FieldID or Link if possible
// Template & Lang & JS?

class FluentExportController extends Controller {

	public static $allowed_actions = array();

	public function init() {
		parent::init();
		$this->getClasses();
		if (!Permission::check('ADMIN')) {
			Security::permissionFailure();
		}
	}

	public function Title() {
		return "Export translated Fields per Langauage";
	}

	public function Locales() {
		if ($Locales = Fluent::locales()) return ViewableData::customise(array("Locale" => $Locales))->renderWith("LangNav");
		return false;
	}

	// requirements didn't work with just a controller
	// todo: smth more light than bootstrap and this ugly file-includes
	public function inliningStyle() {
		if (file_exists("../fluentexport/style/bootstrap.css")) {
			$bootstrapcss = file_get_contents("../fluentexport/style/bootstrap.css");
		} else {
			$bootstrapcss = '';
		}
		if (file_exists("../fluentexport/style/custom.css")) {
			$customcss = file_get_contents("../fluentexport/style/custom.css");
		} else {
			$customcss = '';
		}

		return '<style type="text/css">' . $bootstrapcss .' '. $customcss . '</style>';
	}

	public function Content() {
		$style = $this->inliningStyle();
		$langs = ''; // $this->Locales()
		$tables = $this->FluentClasses();
		$content = $this->showItems();
		return $style . $langs . $tables . $content;
	}

	// Items as html table
	public function showItems() {
		$table = $this->getItems();
		$out = "<table>";
		foreach ($table as $row) {
			$out .= "<tr>";
				foreach($row as $subkey => $subelement){
					$out .= "<td>$subelement</td>";
				}
			$out .= "</tr>";
		}
		$out .= "</table>";
		return $out;

//		$result = ArrayList::create();
//		$table = $this->getItems();
//
//		foreach ($table as $row) {
//			foreach($row as $subkey => $subelement){
//					$r = ArrayData::create(array($subkey => $subelement)); //you can oop over an ArrayList
//					$result->push($r);
//			}
//    	}
//		if ($result) return ViewableData::customise(array("FluentClasses" => $result))->renderWith("ItemTable");
//		return false;
	}

	public function CurrentItem() {
		if ($getVars = $this->getRequest()->getVars()) {
			return $getVars['items'];
		}
	}

	// get all fields translated for a certain class with Ancestrys as array leaded with a linked ID, ClassName
	public function getItems() {
		$getVars = $this->getRequest()->getVars();
		$tableOut = array();
		$header = array(array('ID','ClassName'));
		if (isset($getVars['items'])) {
			$Items = Versioned::get_by_stage($getVars['items'], 'Live');
			$i = 0;
			foreach($Items as $Item) {
				$row = array();
				// header as field names
				if(!isset($j)) {
					foreach($this->getAncestrysTranslatedFields($Item) as $fields) {
						array_push($header, $fields);
					}
				}
				$j = 0;
				foreach($this->getAncestrysTranslatedFields($Item) as $table => $fields) {
					// let ID be a link
					if ($j == 0 && $Item->hasMethod('Link')) {
						array_push($row, '<a = href="'. $Item->Link() .'">' . $Item->ID . '</a>');
					}
					// show just ID
					if ($j == 0 && !$Item->hasMethod('Link')) {
						array_push($row, $Item->ID);
					}
					// add ClassName
					array_push($row, $Item->ClassName);

					foreach($fields as $field) {
						array_push($row, $Item->$field);
					}
					$j++;
				}
				array_push($tableOut, $row);
				$i++;
			}
		}
		$flatheader = array();
		foreach($header as $values) {
			foreach($values as $field) {
				array_push($flatheader, $field);
			}
		}
		debug::dump($flatheader);
//		debug::dump($tableOut['0']);

		return($tableOut);
	}

	// gets TranslatedFields within all ancestries
	public function getAncestrysTranslatedFields($Item) {
		$includedTables = array();
		foreach($Item->getClassAncestry() as $class) {
			// Skip classes without tables
			if(!DataObject::has_own_table($class)) continue;

			// Check translated fields for this class
			$translatedFields = FluentExtension::translated_fields_for($class);
			if(empty($translatedFields)) continue;

			// Mark this table as translatable
			$includedTables[$class] = array_keys($translatedFields);
		}
		return($includedTables);
	}

	// needs specified Extension
	// needs to have own_table
	// nedds to have records
	function getDecoratedBy($extension){
		$classes = array();
		foreach(ClassInfo::subClassesFor('DataObject') as $className) {
			if (DataObject::has_extension("$className", "$extension") && DataObject::has_own_table($className) && $className::get()->Count()) {
				$classes[] = $className;
			}
		}
		return $classes;
	}

	// -> makes sure item has TranslatedFields within all ancestries
	// with this, we return baseClasses and later on add ClassName from DB to the Record - this way, we kinda group similar structured entries and prevent duplicates
	public function getClasses() {
		$BaseClassesWithFluentExtensionsAndFields = array();
		foreach ($this::getDecoratedBy("FluentExtension") as $class) {
			$ancestry = array_values($class::get()->first()->getClassAncestry($class));
			$firstancestry = array_values($ancestry)[0];
			if ($this->getAncestrysTranslatedFields($class::get()->first())) {
				array_push($BaseClassesWithFluentExtensionsAndFields, $firstancestry);
			}
		}
		return array_values(array_unique($BaseClassesWithFluentExtensionsAndFields));
	}

	// get Classes and prepare for Template
	public function FluentClasses()	{
		// $i = $this::getDecoratedBy("FluentExtension");
		$i = $this->getClasses();
		$result = ArrayList::create();
		foreach($i as $Class) {
			$f = DataObject::create();
			$f->Name = $Class;
			$result->push($f);
		}
		if ($result) return ViewableData::customise(array("FluentClasses" => $result))->renderWith("FluentItemTable");
		return false;
	}
}