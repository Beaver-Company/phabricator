<?php

final class ProjectBoardTaskCard extends Phobject {

  private $viewer;
  private $projectHandles;
  private $task;
  private $owner;
  private $canEdit;
  private $coverImageFile;

  public function setViewer(PhabricatorUser $viewer) {
    $this->viewer = $viewer;
    return $this;
  }
  public function getViewer() {
    return $this->viewer;
  }

  public function setProjectHandles(array $handles) {
    $this->projectHandles = $handles;
    return $this;
  }

  public function getProjectHandles() {
    return $this->projectHandles;
  }

  public function setCoverImageFile(PhabricatorFile $cover_image_file) {
    $this->coverImageFile = $cover_image_file;
    return $this;
  }

  public function getCoverImageFile() {
    return $this->coverImageFile;
  }

  public function setTask(ManiphestTask $task) {
    $this->task = $task;
    return $this;
  }
  public function getTask() {
    return $this->task;
  }

  public function setOwner(PhabricatorObjectHandle $owner = null) {
    $this->owner = $owner;
    return $this;
  }
  public function getOwner() {
    return $this->owner;
  }

  public function setCanEdit($can_edit) {
    $this->canEdit = $can_edit;
    return $this;
  }

  public function getCanEdit() {
    return $this->canEdit;
  }

  public function getItem() {
    $task = $this->getTask();
    $owner = $this->getOwner();
    $can_edit = $this->getCanEdit();
    $viewer = $this->getViewer();

    $color_map = ManiphestTaskPriority::getColorMap();
    $bar_color = idx($color_map, $task->getPriority(), 'grey');

    $card = id(new PHUIObjectItemView())
      ->setObject($task)
      ->setUser($viewer)
      ->setObjectName('T'.$task->getID())
      ->setHeader($task->getTitle())
      ->setGrippable($can_edit)
      ->setHref('/T'.$task->getID())
      ->addSigil('project-card')
      ->setDisabled($task->isClosed())
      ->addAction(
        id(new PHUIListItemView())
        ->setName(pht('Edit'))
        ->setIcon('fa-pencil')
        ->addSigil('edit-project-card')
        ->setHref('/maniphest/task/edit/'.$task->getID().'/'))
      ->setBarColor($bar_color);

    if ($owner) {
      $card->addHandleIcon($owner, $owner->getName());
    }

    $cover_file = $this->getCoverImageFile();
    if ($cover_file) {
      $card->setCoverImage($cover_file->getBestURI());
    }

    if (ManiphestTaskPoints::getIsEnabled()) {
      $points = $task->getPoints();
      if ($points !== null) {
        $points_tag = id(new PHUITagView())
          ->setType(PHUITagView::TYPE_SHADE)
          ->setShade(PHUITagView::COLOR_BLUE)
          ->setName($points);
        $card->addAttribute($points_tag);
      }
    }

    if ($task->isClosed()) {
      $icon = ManiphestTaskStatus::getStatusIcon($task->getStatus());
      $icon = id(new PHUIIconView())
        ->setIcon($icon.' grey');
      $card->addAttribute($icon);
      $card->setBarColor('grey');
    }

    $project_handles = $this->getProjectHandles();
    if ($project_handles) {
      $tag_list = id(new PHUIHandleTagListView())
        ->setSlim(true)
        ->setHandles($project_handles);
      $card->addAttribute($tag_list);
    }

    $card->addClass('phui-workcard');

    return $card;
  }

}
