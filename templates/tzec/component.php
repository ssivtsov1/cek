<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language;?>" dir="ltr">
<head>
<jdoc:include type="head" />
<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
<link href="templates/<?php echo $this->template; ?>/favicon.ico" rel="shortcut icon" />
<link href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'/>
<link rel="stylesheet" type="text/css" href="/templates/<?php echo $this->template; ?>/css/popups.css" />
</head>
<body>
    <div class="popup">
        <jdoc:include type="component" />
        <div class="clr"></div>
    </div>
</body>
</html>
