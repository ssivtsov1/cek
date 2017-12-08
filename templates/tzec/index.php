<?php
include_once("analyticstracking.php");
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$doc = JFactory::getDocument();
$app = JFactory::getApplication();
$user = JFactory::getUser();
$lang = JFactory::getLanguage();
jimport('joomla.html.parameter');
JHtml::_('jquery.framework');
$menu = $app->getMenu();
$menuItem = $menu->getActive();
$option = JRequest::getVar('option', null);
$controller = JRequest::getVar('controller', null);
$doc->addScript($this->baseurl.'/templates/'.$this->template.'/js/jquery.colorbox-min.js');
$doc->addScript($this->baseurl.'/templates/'.$this->template.'/js/engine.js');
$doc->addScript($this->baseurl.'/templates/'.$this->template.'/js/jquery.maskedinput.js');
$doc->addScript($this->baseurl.'/templates/'.$this->template.'/js/gmap3.js');

// $scripts = array(
//     '/templates/'.$this->template.'/js/jquery-1.11.3.min.js' => array('mime' => 'text/javascript', 'defer' =>'', 'async' =>''),
//     '/templates/'.$this->template.'/js/jquery-noconflict.js' => array('mime' => 'text/javascript', 'defer' =>'', 'async' =>''),
//     '/templates/'.$this->template.'/js/jquery.colorbox-min.js' => array('mime' => 'text/javascript', 'defer' =>'', 'async' =>''),
//     '/templates/'.$this->template.'/js/owl.carousel.min.js' => array('mime' => 'text/javascript', 'defer' =>'', 'async' =>''),
//     '/templates/'.$this->template.'/js/engine.js' => array('mime' => 'text/javascript', 'defer' =>'', 'async' =>'')
// );
// $this->_scripts = $scripts + $this->_scripts;
if($menu->getActive()->id == $menu->getDefault($lang->getTag())->id) {

};

$this->_generator='';
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language;?>" dir="ltr">
<head>

<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, height=device-height" />
<jdoc:include type="head" />

<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/favicon.ico" rel="shortcut icon" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" />
<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&amp;subset=cyrillic" rel="stylesheet">

    <!--[if lt IE 9]>
        <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
    <![endif]-->



<script type="text/javascript">
(function ($) {

        $(document).ready(function () {
            var maps_zoom = 9,
                maps_center = [48.498460, 35.243950], 
                maps = $('#map-google').gmap3({
                    map: {
                        options: {
                            center: maps_center,
                            zoom: maps_zoom,
                            navigationControl: true
                        }
                    },
                    marker: {
                        values: [
                            {
                                latLng: [48.446234, 35.002481],  
                                data: "м. Дніпро, вул.Кедріна,28",                               
                                options: {icon: "/templates/tzec/images/Logo.png"}
                            },
                            {
                                latLng: [48.396079, 34.973960], 
                                data: "м.Дніпро, вул.Бориса Кротова, 44",                               
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            },
                            {
                                latLng: [47.649645, 33.715329],  
                                data: "м.Апостолове, вул.Лесі Українки, 37",                                 
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            },
                            {
                                latLng: [48.484311, 34.013878], 
                                data: "м. Вільногірськ, вул. Центральна, 39",                               
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            },
                            {
                                latLng: [48.735750, 35.326694],  
                                data: "Новомосковський р-н, смт Гвардійське, вул.Сетьова,10",                             
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            },
                            {
                                latLng: [48.342484, 33.509025],
                                data: "м. Жовті Води, вул. Хмельницького, 32",                                
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            }, 
                            {
                                latLng: [47.686725, 33.155682],
                                data: "м. Кривий Ріг, вул. Недєліна 43А",                                
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            },
                            {
                                latLng: [48.090671, 33.393019],
                                data: "м. Кривий Ріг, с. Мирівське, вул. Двінська 8/1",                                
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            }, 
                            {
                                latLng: [48.494720, 35.941855],
                                data: "м. Павлоград, вул. Нова, 5",                                
                                options: {icon: "/templates/tzec/images/lamp.png"}
                            },                              
                        ],
                        options: {
                            draggable: false
                        },
                        events: {
                            mouseover: function (marker, event, context) {
                                var map = $(this).gmap3("get"),
                                    infowindow = $(this).gmap3({get: {name: "infowindow"}});
                                if (infowindow) {
                                    infowindow.open(map, marker);
                                    infowindow.setContent(context.data);
                                } else {
                                    $(this).gmap3({
                                        infowindow: {
                                            anchor: marker,
                                            options: {content: context.data}
                                        }
                                    });
                                }
                            },
                            mouseout: function () {
                                var infowindow = $(this).gmap3({get: {name: "infowindow"}});
                                if (infowindow) {
                                    infowindow.close();
                                }
                            }
                        }
                    }
                });

            $('#map-button-click').on('click', 'a', function (event) {
                event.preventDefault();

                var mark = $(this);

                if (mark.hasClass('clear')) {
                    maps.gmap3({
                        map: {
                            options: {
                                center: maps_center,
                                zoom: maps_zoom,
                            }
                        }
                    });
                } else {
                    maps.gmap3({
                        map: {
                            options: {
                                center: [
                                    mark.data('lat'),
                                    mark.data('lng')
                                ],
                                zoom: 11,
                            }
                        }
                    });
                }
            });
        });

    })(jQuery);

</script>
  
</head>
<body class="<?php echo $menuItem->params->get('pageclass_sfx'); ?>">   

<div id="page">
 
      <div class="head-wrap">
        <div class="head">
          <div class="wrap">
            <div class="ticker">
              <jdoc:include type="modules" style="joexhtml" name="ticker" />
            </div>
            <div class="logo-wrap">
              <div class="logo">              
                 <jdoc:include type="modules" style="joexhtml" name="logo" />              
              </div>
              <div class="name-company">              
                  <jdoc:include type="modules" style="joexhtml" name="name-company" />              
              </div>
            </div>
            <div class="head-info">
              <div class="search">                
                <div class="rp"></div>
                <div class="rr"></div>              
                  <jdoc:include type="modules" style="joexhtml" name="search" />             
              </div>
              <div class="head-phone">              
                  <jdoc:include type="modules" style="joexhtml" name="head-phone" />              
              </div>
              <div class="languages">              
                  <jdoc:include type="modules" style="joexhtml" name="languages" />              
              </div>
            </div>
            <div class="slidetoogle"></div>    
            <div class="menu-top">
                <jdoc:include type="modules" style="xhtml" name="menu-top" />
            </div>
          </div>                                   
        </div>
      </div>
      <div class="slider-wrap">
        <div class="slider">              
          <jdoc:include type="modules" style="joexhtml" name="slider" />
          <div class="background-slide">
              <div class="left"></div>
              <div class="right"></div>
          </div>              
        </div>
      </div>            

      <div class="services">
        <div class="wrap">
          <jdoc:include type="modules" style="joexhtml" name="services" />
        </div>
      </div>

       <div class="main-news-title">
          <div class="wrap">
            <jdoc:include type="modules" style="xhtml" name="main-news-title" />
          </div>
        </div>

        <div class="main-news">
          <div class="wrap">
            <jdoc:include type="modules" style="xhtml" name="main-news" />
          </div>
        </div>

      <div class="wrap presswrap">
        <div class="news">              
            <jdoc:include type="modules" style="joexhtml" name="news" />              
        </div>        
        <div id="form">          
            <jdoc:include type="modules" style="joexhtml" name="form" />                      
        </div>
        <div class="video-main">                   
            <jdoc:include type="modules" style="joexhtml" name="video-main" />                      
        </div>
        <div class="press">        
          <jdoc:include type="modules" style="joexhtml" name="press" />        
        </div>
      </div>
      
      <div id="content">     
          <div class="wrap">
              <div id="breadcrumbs">
                  <div class="wrap">
                     <jdoc:include type="modules" style="xhtml" name="breadcrumbs" />                   
                 </div>
              </div>              
           
              <div id="left">
                 <jdoc:include type="modules" style="joexhtml" name="left-column" />               
              </div>
                   
              <div id="component">
                <jdoc:include type="component"/>
              </div>              

              <div id="right">
                <jdoc:include type="modules" style="joexhtml" name="right-column" />               
              </div>      
              

              <?php
              if (($this->countModules('right-column')==0) and ($this->countModules('left-column')==0)){
                $column = 'all-hidden';
              }elseif ($this->countModules('right-column')==0){
                $column = 'right-hidden';
              }elseif ($this->countModules('left-column')==0){
                $column = 'left-hidden';
              }           
              ?>
             <body class="<?php if (isset($column)) echo $column ?>">
        </div>
      </div>
      

      <div class="video">              
        <jdoc:include type="modules" style="joexhtml" name="video" />              
      </div>

      <div class="text">        
          <jdoc:include type="modules" style="joexhtml" name="text" />        
      </div>

      
  
</div><!--end page-->
      <div class="map">        
          <jdoc:include type="modules" style="joexhtml" name="map" />        
      </div>


    <div id="footer">
      <div class="wrap">
        <div class="footer-logo">
            <jdoc:include type="modules" style="xhtml" name="footer-logo" />
        </div>       
        <div class="footer-menu1">
            <jdoc:include type="modules" style="xhtml" name="menu-footer1" />
        </div>
        <div class="footer-menu2">
            <jdoc:include type="modules" style="xhtml" name="menu-footer2" />
        </div>
        <div class="footer-cont">
            <jdoc:include type="modules" style="xhtml" name="menu-cont" />
        </div>
        <div class="footer-brands">
            <jdoc:include type="modules" style="xhtml" name="menu-brands" />
        </div>
      </div>        
    </div>

    <div class="footer-copy">
      <div class="wrap">        
        <jdoc:include type="modules" style="joexhtml" name="footer-copy" /> 
      </div>       
    </div>


<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/colorbox.css" property="stylesheet" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/tooltip.css" property="stylesheet" />
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&amp;language=ru&amp;key=AIzaSyCio9wud5U4kI-Q_da8fgJ1yBSzFVvyFjo"></script>

<jdoc:include type="modules" name="floatercontainer" style="xhtml" />  
</body>
</html>



