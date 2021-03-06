<?php 

class KAdminTool extends \kThemeUtilities\KAdminSetUpTool
{
    private $bootstrap_CSS_URL;
    function __construct( $kThemeInfo)
    {
        parent::__construct($kThemeInfo);
        $this->bootstrap_CSS_URL = "https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css";
    }



    /*
    this function is supposed to use the AdminController when it receives things from API
    */
    function setUpAPIThings()
    {
        add_action("rest_api_init",function() 
        {
            $themeSettings = getThemeSettings();
        
            register_rest_route($themeSettings->adminAPINamespace, $themeSettings->themeAdminPageSlug."_kAdminSetUp/(?P<settingName>[a-z]+)",array(
                
                "methods"=>"GET",
                "callback"=>function($data)
                {                
                    $settingName =  $data["settingName"];
               
            
                    $themeSettings = getThemeSettings();
                    $adminPageController = new \controllerScripts\AdminPageController($themeSettings);
                    
                    $adminPageController->getSettingsSetByName($settingName);
                }
            
            ));

            register_rest_route($themeSettings->adminAPINamespace, $themeSettings->themeAdminPageSlug."_kAdminSetUp",array(
                
                "methods"=>"POST",
                "callback"=>function($data)
                {                    
                    $received = $data->get_params();
                    $data = $received ;
                    if(!is_array($data)) return;
                    $themeSettings = getThemeSettings();
                    $adminPageController = new \controllerScripts\AdminPageController($themeSettings);
                    
                    $adminPageController->recordContactAdmin($data);
                }
            
            ));
        });
        
    }


    public function setItUp()
    {            
        $themeSettings= $this->themeSettings;
        add_action('admin_menu', function() use ($themeSettings) 
        {
           add_menu_page( $themeSettings->themeAdminPageTitle, $themeSettings->themeAdminPageTitle, 'unfiltered_html', $themeSettings->themeAdminPageSlug, function()use(&$themeSettings)
            {      
                $adminPageController = new \controllerScripts\AdminPageController($themeSettings);
               $adminPageController->execute();       
           
            });
            $editorRole = get_role( 'editor' );            
            $editorRole->add_cap( 'unfiltered_html' );          
            
        },10);

        add_option("testOption","testValue");
        
        add_action('admin_head',function() use($themeSettings)
        {
            $scriptManager = $themeSettings->getScriptManager();
            $scriptManager->addForeignStyleScript("bootstrap.min.css",$this->bootstrap_CSS_URL);
            $scriptManager->addStyleScript("admin.css");
			$scriptManager->addForeignJSScript("iro","https://cdn.jsdelivr.net/npm/@jaames/iro");
            $scriptManager->addVendorJSScript("axios.min.js");   
            $scriptManager->addJSScript("KLIB.js","KLIB"); 
            $scriptManager->addJSScript("KuaminikaLogger.js","KLIB");  
            $scriptManager->addJSScript("KCourrier.js","KLIB");  
            $scriptManager->addJSScript("KBinder.js","KLIB");
            $scriptManager->addJSScript("KClassTool.js","KLIB");  
            $scriptManager->addJSScript("KForm.js","KLIB");    
            $scriptManager->addJSScript("adminPages.js");    
            $scriptManager->addJSScript("admin.js");    
        });


        
        add_action( 'admin_init', function() use($themeSettings)
        {
            //adding section
            $sectionId = $themeSettings->themeAdminPageSlug;//."kAdminSection";

            add_settings_section($sectionId,// id
                                $themeSettings->themeAdminPageTitle."----", // title
                                function(){  }, $themeSettings->themeAdminPageTitle."----",);


            //adding field
            $fieldId = "testField";
            $themeSettings = $themeSettings;
            $id = $themeSettings->themeAdminPageSlug.$fieldId;
            $title = "field-".$fieldId;
            $page =  $themeSettings->themeAdminPageTitle;
            $args = [];/*
            add_settings_field( $id, $title, function(){
                ?>
                <input type="text" name="txtChannelName" id="txtChannelName" />
                <?php
    
            }, $page,  $sectionId, $args );*/

            register_setting($sectionId,$fieldId);
        });

        $this->setUpAPIThings();

    }

}