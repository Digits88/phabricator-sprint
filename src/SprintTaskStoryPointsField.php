<?php
/**
 * Copyright (C) 2014 Michael Peters
 * Licensed under GNU GPL v3. See LICENSE for full details
 */

final class SprintTaskStoryPointsField extends ManiphestCustomField
  implements PhabricatorStandardCustomFieldInterface {

  public function __construct() {
    $proxy = id(new PhabricatorStandardCustomFieldText())
      ->setFieldKey($this->getFieldKey())
      ->setApplicationField($this)
      ->setFieldConfig(array(
        'name' => $this->getFieldName(),
        'description' => $this->getFieldDescription(),
      ));

    $this->setProxy($proxy);
  }

  public function canSetProxy() {
    return true;
  }

  // == General field identity stuff
  public function getFieldKey() {
    return 'isdc:sprint:storypoints';
  }

  public function getFieldName() {
    return 'Story Points';
  }

  public function getFieldDescription() {
    return 'Estimated story points for this task';
  }

  public function getStandardCustomFieldNamespace() {
    return 'maniphest';
  }

  public function showField() {
    static $show = null;

    $viewer = $this->getViewer();

    if ($show == null) {
      $id = $this->getObject()->getID();
      $task = id(new ManiphestTaskQuery())
          ->setViewer($viewer)
	  ->withIds(array($id))
	  ->needProjectPHIDs(true)
	  ->executeOne();
      if (!($task instanceof ManiphestTask)) {
        return $show = false;
      }
      $project_phids = $task->getProjectPHIDs();
      if (empty($project_phids)) {
        return $show = false;
      }
      // Fetch the names from all the Projects associated with this task
      $projects = id(new PhabricatorProject())
        ->loadAllWhere(
        'phid IN (%Ls)',
        $project_phids);
      $names = mpull($projects, 'getName');

      // Set show to true if one of the Projects contains "Sprint"
      $show = false;
      foreach($names as $name) {
        if (strpos($name, 'Sprint') !== false) {
          $show = true;
        }
      }
    }

    return $show;
  }

  public function renderPropertyViewLabel() {
    if (!$this->showField()) {
      return;
    }

    if ($this->getProxy()) {
      return $this->getProxy()->renderPropertyViewLabel();
    }
    return $this->getFieldName();
  }

  public function renderPropertyViewValue(array $handles) {
    if (!$this->showField()) {
      return;
    }

    if ($this->getProxy()) {
      return $this->getProxy()->renderPropertyViewValue($handles);
    }
    throw new PhabricatorCustomFieldImplementationIncompleteException($this);
  }

  public function shouldAppearInEditView() {
    return true;
  }

  public function renderEditControl(array $handles) {
    if (!$this->showField()) {
      return;
    }

    if ($this->getProxy()) {
      return $this->getProxy()->renderEditControl($handles);
    }
    throw new PhabricatorCustomFieldImplementationIncompleteException($this);
  }

  // == Search
  public function shouldAppearInApplicationSearch()
  {
    return true;
  }

}
