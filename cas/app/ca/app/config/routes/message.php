<?php
$router->addGet("/message[/]?",
    array(
        "controller" => "Message",
        "action" => "index",
    )
);

$router->addGet("/message/getMsgByUid[/]?",
    array(
        "controller" => "Message",
        "action" => "getMsgByUid",
    )
);

$router->addGet("/message/getUnreadCount[/]?",
    array(
        "controller" => "Message",
        "action" => "getUnreadCount",
    )
);

$router->addGet("/message/getTypeUnreadCount[/]?",
    array(
        "controller" => "Message",
        "action" => "getTypeUnreadCount",
    )
);

$router->addGet("/message/getMsgDetail[/]?",
    array(
        "controller" => "Message",
        "action" => "getMsgDetail",
    )
);

$router->addGet("/message/msgDelete[/]?",
    array(
        "controller" => "Message",
        "action" => "msgDelete",
    )
);

$router->addGet("/message/msgRead[/]?",
    array(
        "controller" => "Message",
        "action" => "msgRead",
    )
);

$router->addGet("/message/getAllMsgByUid[/]?",
    array(
        "controller" => "Message",
        "action" => "getAllMsgByUid",
    )
);