<?php
// todo:
//subitems in navigation
// has_ons -> FieldID Link if possible -> icons? what is in bootstrap?
// DO vs. ArrayData
// lang-switch
// 		http://v4-alpha.getbootstrap.com/components/dropdowns/
// make additional fields like id & ClassName configurable
// sort vs. hierarchical
// CSV-Export and complete (all tables) archive.tar.gz
// 		home-dashboard for exporting
// does FluentContentController extension make sense
// rename config file
// make live vs. stage selectable
//		show warning if a live-record has a newer staged version
// include Template & Lang & JS for export?

class FluentExportController extends Controller {

	public static $allowed_actions = array();

	public function init() {
		parent::init();
		static::requirements();
		if (!Permission::check('ADMIN')) {
			Security::permissionFailure();
		}
	}

	public function Title() {
		return "Export translated Fields";
	}

	// todo: be meaningful
	public function LangNav() {
		if ($langs = Fluent::locales()) {
			$ArrayList = new ArrayList();
			foreach	($langs as $lang) {
				$f = DataObject::create();
				$f->Lang = $lang;
				$ArrayList->push($f);
			}
			if ($ArrayList) return($ArrayList);
			return false;
		}
	}

	public static function requirements() {
		Requirements::css('fluentexport/style/bootstrap.css');
		Requirements::css('fluentexport/style/custom.css');
	}

	// used for navigation - todo: remove and use the same getter as for tables
	public function ContentTables()	{
		$result = $this->getItems();
		if ($result) return($result);
		return false;
	}

	// Items for Template
	public function showItems() {
		$tables = $this->getItems();
		$result = ArrayList::create();
		if($tables) {
			foreach ($tables as $table) {
				$topush = ArrayList::create();
				foreach ($table as $row) {
					$topushNest1 = ArrayList::create();
					foreach($row as $subkey => $subelement){
						$topushNest1->push(array('item' => $subelement));
					}
					$topush->push($topushNest1);
				}
				$result->push($topush);
			}
		}
		if ($result) return($result);
		return false;
	}

	// navigation
	public function CurrentItem() {
		if ($getVars = $this->getRequest()->getVars()) {
			if (isset($getVars['items'])) {
				return $getVars['items'];
			}
		}
	}

	// get all fields translated for a certain class with Ancestries as array leaded with a ID & ClassName
	public function getItems()
	{
		$getVars = $this->getRequest()->getVars();
		$descentClasses = array();
		$allTables = array();
		if (isset($getVars['items'])) {
			$descentClasses = array_unique($Items = Versioned::get_by_stage($getVars['items'], 'Live')->Column("ClassName"));
			$allItems = Versioned::get_by_stage($getVars['items'], 'Live');
			foreach ($descentClasses as $table) {
				$tableOut = array();
				$flatheader = array();
				$header = array(array('ID', 'ClassName'));
				$Items = $allItems->filter("ClassName", $table);
				$i = 0;
				foreach($Items as $Item) {
					$j = 0;
					$row = array();

					// header as field names
					foreach($this->getAncestrysTranslatedFields($Item) as $fields) {
						array_push($header, $fields);
					}

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

					foreach($this->getAncestrysTranslatedFields($Item) as $table => $fields) {
						foreach($fields as $field) {
							array_push($row, $Item->$field);
						}
						$j++;
					}
					array_push($tableOut, $row);
					$i++;
				}
				foreach($header as $values) {
					foreach($values as $field) {
						array_push($flatheader, $field);
					}
				}
				$flatheader = array_unique(array_values($flatheader));
				$allTables["$table"] = array_merge(array($flatheader),$tableOut);
				//debug::dump($allTables["$table"]);
			}
			return $allTables;
		}
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
		if ($result) return($result);
		return false;
	}
}