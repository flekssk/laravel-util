<?php

declare(strict_types=1);

namespace FKS\Instructions\Enums;

enum InstructionsActionEnum: string
{
    case PROVIDE_DATA = 'provide_data';
    case DISPLAY_ERRORS = 'display_errors';
    case REDIRECT = 'redirect';
    case SUCCESS = 'success';
    case FAIL = 'fail';
    case CHANGE_ENTITY = 'change_entity';
    case CALL_SERVICE = 'call_service';
    case GET_ENTITY = 'get_entity';
    case GET_ENTITIES_LIST = 'get_entities_list';
    case SELECT_ONE_OF_SUBACTION = 'select_one_of_subaction';
}
