<?php

namespace BucklesHusky\ElementalSoundcloud\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class LeftAndMainExt extends Extension {
    
    public function init() {
        Requirements::css('buckleshusky/silverstripe-elemental-soundcloud: icons/icons.css');
    }
    
}