<?php
try {


    if (!isset($config) || !isset($config['PROJECT'])) throw new Exception("\$config['PROJECT'] has not been set in index.php");

    $isDevMode = isset($_GET['dev']);

    $PROJECT_BASE_DIR = "";

    $SPACE_LIB_JS_URL = "http://localhost:9007/index.js";
    $PROJECT_NAME = $config['PROJECT'];
    $EMBED_GLOBAL_NAME = "__spacecms_global";
    $INJECT_AFTER_ANCHOR_REFERENCE = "<head>";
    $EMBED_HEAD_SNIPPET_FILE = "lib/embed-head-snippet.html";
    $SPACE_DATA_PREFIX = "space";
    $TWIG_VENDOR_DIR = '../vendor/autoload.php';
//error_reporting(E_ERROR | E_PARSE);

    if ($isDevMode) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    if (!isset($_GET['uri']) || empty($_GET['uri']) || strlen($_GET['uri']) <= 0)
        $uri = "index";
    else
        $uri = $_GET['uri'];


    /*
    $parts = explode('/', $uri);



    if ($parts[0] !== "project") die("Invalid URI. project/ expected");

    if (!$parts[1] || strlen($parts[1]) <= 0) die("Invalid project name");

    if (!is_dir("project/".$parts[1])) die("Project does not exist");

    $projectName = $parts[1];*/

//require_once('/var/www/twig/autoload.php');
    require_once($TWIG_VENDOR_DIR);


    $loader = new Twig_Loader_Filesystem("./");
    $twig = new Twig_Environment($loader, array(
        //'cache' => 'cache',
        'debug' => $isDevMode,
        'strict_variables' => $isDevMode
    ));

    if ($isDevMode) {
        ///if in dev mode, ignore lexer tags for js parsing by forcing the php twig compiler to not recognise the tags.
        /// This means that if you want anything rendered before js live rendering - use these tags below.
        $twig->setLexer(new Twig_Lexer($twig, array(
            'tag_variable' => array('{[', ']}'),
        )));
    }


    $data = file_get_contents("http://portal.firepit.tech/api/v1/project/${PROJECT_NAME}/spaces");
    $data = json_decode($data, true);
    $data[$SPACE_DATA_PREFIX] = $data;
    $data = array_merge($data, array(
            'config' => array(
                'api_url' => "http://portal.firepit.tech/api/v1/",
                "env" => $isDevMode ? "development" : "production",
                "space_update_cooldown" => 0
            ),
            'project' => array(
                'name' => $PROJECT_NAME
            )
        )
    );
    /*
     *
     * config: {
                            api_url: API_URL,
                            env: process.env.NODE_ENV,
                            space_update_cooldown: DEFAULT_SPACE_UPDATE_COOLDOWN
                        },
     */
    $dataEncoded = json_encode($data);

    $embedHeadSnippet = "";

    if ($isDevMode) {

        $embedHeadSnippet = "<script>window['${EMBED_GLOBAL_NAME}']=${dataEncoded};</script>";

        $embedHeadSnippet .= "<script async>document.addEventListener('DOMContentLoaded',function() {window.document.body.innerHTML+='<div style=\"font-family:sans-serif; position:fixed; z-index:10050; top:0; left:0; width:100%; height:100%; background-color:white; color:black; display:flex; align-items:center; justify-content:center;\" id=\"cms-loading-dialog\">(CMS Dev Mode) Loading page...</div>';});</script>
";
        $embedHeadSnippet .= "<script src=\"${SPACE_LIB_JS_URL}\"></script>";

    }

//http://localhost:1337/project/paulweller.com/spaces

    $alts = array(
        //  '{uri}.php',
        '{uri}.html',
        '{uri}.twig',
        '{uri}.html.twig',
    );

    $i = 0;

    $foundFile = false;

    $notFound = array();

    $requestedFile = "";

    for ($i = 0; $i < count($alts); $i++) {
        $requestedFile = $PROJECT_BASE_DIR . str_replace('{uri}', $uri, $alts[$i]);
        if (is_file($requestedFile)) {
            $foundFile = file_get_contents($requestedFile);
            break;
        } else {
            array_push($notFound, $requestedFile);
        }
    }


    if ($foundFile) {
        $pageHTML = $foundFile;

        if (stripos($requestedFile, ".twig") > 0) {
            try {
                //print_r($spaceData);


                if ($isDevMode) {

                    //$pageHTML="{% verbatim %}".$pageHTML;
                    //$pageHTML=$pageHTML."{% endverbatim %}";

                    $template = $twig->createTemplate($pageHTML);


                    //print_r($template->getSourceContext()->getCode());die();

                    $pageHTML = $template->render($data);


                } else {
                    $pageHTML = $twig->render($requestedFile, $data);
                }


            } catch (\Exception $e) {
                print_r($e->getMessage());


            }
        }


        echo str_replace($INJECT_AFTER_ANCHOR_REFERENCE, $INJECT_AFTER_ANCHOR_REFERENCE . $embedHeadSnippet, $pageHTML);

    } else {
        for ($i = 0; $i < count($notFound); $i++) {
            echo "Not found: " . $notFound[$i] . "<br/>";
        }
    }

}catch (\Exception $e){
    if ($isDevMode){
        throw $e;
    }else {
        echo $e->getMessage();
    }
}


//echo $twig->render('index.html.twig', array('test' => 'blah blah'));