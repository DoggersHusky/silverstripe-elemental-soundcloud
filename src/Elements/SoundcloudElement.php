<?php

namespace BucklesHusky\ElementalSoundcloud\Elements;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Forms\TextField;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDate;

class SoundcloudElement extends BaseElement {
    
    //icon
    private static $icon = 'soundcloud-icon';
    
    private static $singular_name = 'SoundCloud';
    
    private static $plural_name = 'SoundClouds';
    
    private static $table_name = 'ElementalSoundcloud';
    
    private static $Description = 'A Soundcloud block';
    
    private static $db = [
        'SoundcloudURL' => 'Text'
    ];
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $fields->removeByName('SoundcloudURL');
        
        $fields->addFieldToTab('Root.Main', 
                TextField::create('SoundcloudURL','SoundCloud URL')->setDescription('example: https://soundcloud.com/scumgang6ix9ine/waka-ft-a-boogie-wit-da-hoodie')
        );
        
        if ($this->SoundcloudURL) {
            $fields->addFieldToTab('Root.Main', 
                \SilverStripe\Forms\LiteralField::create('SoundCloudSample',''.$this->getSoundCloud())
            );
        }

        return $fields;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return _t(__CLASS__.'.BlockType', 'SoundCloud');
    }
    
    public function getSoundCloud() {
        //make sure there is something set
        if ($this->SoundcloudURL) {
            
            //get the cache
            $cache = Injector::inst()->get(CacheInterface::class . '.elementalsoundcloud');
            
            //get the date
            $date = DBDate::create();
            $date->setValue($this->LastEdited);
            
            //the cache key
            $cacheKey = implode(['soundCloud', $this->ID, $date->Nice()]);
            
            //try to get the cache
            $data = $cache->get($cacheKey);
            
            if (!$data) {
                $iframe = $this->getURLContents('https://soundcloud.com/oembed?maxheight=500&auto_play=true&format=json&url='.$this->SoundcloudURL);
                $iframe = json_decode($iframe);

                $data = new DBHTMLText('Sound');
                $data->setValue($iframe->html);
                
                $cache->set($cacheKey, $data);
            }

            return $data;
        }
    }
    
    /**
     * Loads the contents of a url, with sensitivity for allow_url_fopen being off
     * @param {string} $url URL to load
     * @param {bool} $quickTimeout Whether to time out quickly or wait till the request completes, if quick time out is specified false is returned
     * @param {array} $headers Headers to pass through to curl
     * @return {mixed} Returns the contents of the loaded url or false
     */
    final protected function getURLContents($url, $quickTimeout=false, $headers=false) {
        if(function_exists('curl_init') && $ch = curl_init()) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if($quickTimeout) {
                curl_setopt($ch, CURLOPT_TIMEOUT, 0.1);
            }
    
            if($headers!==false) {
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
    
            $contents=curl_exec($ch);
            curl_close($ch);
        }else {
            user_error('Could not use to curl', E_USER_ERROR);
        }
    
        return $contents;
    }
    
}
