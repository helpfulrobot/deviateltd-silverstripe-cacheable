<?php
/**
 * Created by PhpStorm.
 * User: normann.lou
 * Date: 15/03/2015
 * Time: 6:47 PM
 */

class CachedNavigation extends ArrayList {
    private $cached_site_config;
    private $site_map = array();
    private $root_elements = array();
    private $locked = false;

    public function set_site_config($cached_site_config){
        $this->cached_site_config = $cached_site_config;
    }

    public function get_site_config() {
        return $this->cached_site_config;
    }

    public function set_site_map($site_map){
        $this->site_map = $site_map;
    }

    public function get_site_map() {
        return $this->site_map;
    }

    public function set_root_elements($root_elements) {
        $this->root_elements = $root_elements;
    }

    public function get_root_elements() {
        return $this->root_elements;
    }

    public function lock(){
        $this->locked = true;
    }

    public function unlock(){
        $this->locked = false;
    }

    public function isLocked(){
        return $this->locked === true;
    }

    public function isUnlocked() {
        return $this->locked === false;
    }

    public function Menu($level=1){
        if($level == 1) {
            $root_elements = new ArrayList($this->get_root_elements());
            $result = $root_elements->filter(array(
                    "ShowInMenus" => 1
            ));
        } else {
            $dataID = Director::get_current_page()->ID;
            $site_map = $this->get_site_map();
            if(isset($site_map[$dataID])){
                $parent = $site_map[$dataID];

                $stack = array($parent);
                if($parent){
                    while($parent = $parent->getParent()){
                        array_unshift($stack, $parent);
                    }
                }

                if(isset($stack[$level-2])) {
                    $elements = new ArrayList($stack[$level-2]->getAllChildren());
                    $result = $elements->filter(
                        array(
                            "ShowInMenus" => 1,
                        )
                    );
                }
            }
        }

        $visible = array();

        // Remove all entries the can not be viewed by the current user
        // We might need to create a show in menu permission
        if(isset($result)) {
            foreach($result as $page) {
                if($page->canView()) {
                    $visible[] = $page;
                }
            }
        }
        return new ArrayList($visible);
    }

    function getAncestores($cachedID){
        $site_map = $this->get_site_map();
        $ancestors = new ArrayList();
        if(isset($site_map[$cachedID])) {
            $parent = $site_map[$cachedID];
            while($parent = $parent->getParent()){
                $ancestors->push($parent);
            }
        }
        return $ancestors;
    }
}