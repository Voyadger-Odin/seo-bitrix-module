<?php

namespace Voyadger\Seo;


class event
{
    public static $MODULE_ID = 'voyadger.seo';

    // Update tags
    public static function eventHandlerTagsOverwrite(){
        global $APPLICATION;

        $result = UrlsTable::getList(array(
            'select' => array('ID', 'active', 'title', 'keywords', 'description', 'h1', 'text'),
            'filter' => array('url' => $APPLICATION->GetCurPage()),
            'limit' => 1
        ));

        while ($row = $result->fetchObject()){
            if (!$row->getActive()){break;}

            $APPLICATION->SetPageProperty('title', $row->getTitle());
            $APPLICATION->SetPageProperty('keywords', $row->getKeywords());
            $APPLICATION->SetPageProperty('h1', $row->getH1());
            $APPLICATION->SetPageProperty('description', $row->getDescription());
            $APPLICATION->SetPageProperty('text', $row->getText());
        }
    }

    // Redirects
    public static function eventhandlerRedirect(){
        global $APPLICATION;

        $result = RedirectsTable::getList(array(
            'select' => array('ID', 'active', 'url_from', 'url_to', 'redirect_type'),
            'filter' => array('url_from' => explode('?', $_SERVER['REQUEST_URI'])[0]),
            'limit' => 1
        ));

        while ($row = $result->fetchObject()){
            if (!$row->getActive()){break;}

            LocalRedirect(
                $row->getUrlTo() . '?' . explode('?', $_SERVER['REQUEST_URI'])[1],
                true, $row->getRedirectType()
            );
            \CMain::FinalActions();
            die();
        }
    }

}