PHP_FUNCTION(oel_add_begin_while) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);

    znode *while_token= oel_create_token(res_op_array, OEL_TYPE_TOKEN_WHILE TSRMLS_CC);
    while_token->u.opline_num= get_next_op_number(res_op_array->oel_cg.active_op_array);
}

PHP_FUNCTION(oel_add_begin_while_body) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_WHILE)) oel_compile_error(E_ERROR, "token is not of type while");

    znode *token= oel_create_token(res_op_array, OEL_TYPE_TOKEN_WHILE_BODY TSRMLS_CC);
    znode *expr=  oel_stack_pop_operand(res_op_array TSRMLS_CC);

    php_oel_saved_env *env= oel_env_prepare(res_op_array TSRMLS_CC);
    zend_do_while_cond(expr, token TSRMLS_CC);
    oel_env_restore(res_op_array, env TSRMLS_CC);
}

PHP_FUNCTION(oel_add_end_while) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_WHILE_BODY)) oel_compile_error(E_ERROR, "token is not of type while body");

    znode *body_token=  oel_stack_pop_token(res_op_array TSRMLS_CC);
    znode *while_token= oel_stack_pop_token(res_op_array TSRMLS_CC);

    php_oel_saved_env *env= oel_env_prepare(res_op_array TSRMLS_CC);
    zend_do_while_end(while_token, body_token TSRMLS_CC);
    oel_env_restore(res_op_array, env TSRMLS_CC);
}

PHP_FUNCTION(oel_add_begin_dowhile) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);

    znode *token= oel_create_token(res_op_array, OEL_TYPE_TOKEN_DOWHILE TSRMLS_CC);
    token->u.opline_num= get_next_op_number(res_op_array->oel_cg.active_op_array);
    znode *body_token= oel_create_token(res_op_array, OEL_TYPE_TOKEN_DOWHILE_BODY TSRMLS_CC);

    php_oel_saved_env *env= oel_env_prepare(res_op_array TSRMLS_CC);
    zend_do_do_while_begin(TSRMLS_CC);
    oel_env_restore(res_op_array, env TSRMLS_CC);
}

PHP_FUNCTION(oel_add_end_dowhile_body) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_DOWHILE_BODY)) oel_compile_error(E_ERROR, "token is not of type dowhile body");

    znode *body_token= oel_stack_top_token(res_op_array TSRMLS_CC);
    body_token->u.opline_num= get_next_op_number(res_op_array->oel_cg.active_op_array);
}

PHP_FUNCTION(oel_add_end_dowhile) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);

    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_DOWHILE_BODY)) oel_compile_error(E_ERROR, "token is not of type dowhile body");
    znode *body_token= oel_stack_pop_token(res_op_array TSRMLS_CC);
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_DOWHILE)) oel_compile_error(E_ERROR, "token is not of type dowhile");
    znode *token= oel_stack_pop_token(res_op_array TSRMLS_CC);
    znode *expr= oel_stack_pop_operand(res_op_array TSRMLS_CC);

    php_oel_saved_env *env= oel_env_prepare(res_op_array TSRMLS_CC);
    zend_do_do_while_end(token, body_token, expr TSRMLS_CC);
    oel_env_restore(res_op_array, env TSRMLS_CC);
}

PHP_FUNCTION(oel_add_begin_foreach) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);

    znode *array= oel_stack_pop_operand(res_op_array TSRMLS_CC);
    int is_variable= (array->op_type == IS_CV);
    if (is_variable) {
        if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_VARIABLE)) oel_compile_error(E_ERROR, "oel_add_begin_foreach without oel_add_begin_variable_parse");
        oel_stack_pop_token(res_op_array TSRMLS_CC);
    }
    znode *foreach_token=       oel_create_token(res_op_array, OEL_TYPE_TOKEN_FOREACH      TSRMLS_CC);
    znode *as_token=            oel_create_token(res_op_array, OEL_TYPE_UNSET              TSRMLS_CC);
    znode *open_brackets_token= oel_create_token(res_op_array, OEL_TYPE_TOKEN_FOREACH_BODY TSRMLS_CC);

    php_oel_saved_env *env= oel_env_prepare(res_op_array TSRMLS_CC);
    zend_do_foreach_begin(foreach_token, open_brackets_token, array, as_token, is_variable TSRMLS_CC);
    oel_env_restore(res_op_array, env TSRMLS_CC);
}

PHP_FUNCTION(oel_add_begin_foreach_body) {
    zval      *arg_op_array;
    zend_bool  arg_ref= 0;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r|b!", &arg_op_array, &arg_ref) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_VARIABLE)) oel_compile_error(E_ERROR, "oel_add_begin_foreach_body without oel_add_begin_variable_parse (for value)");

    oel_stack_pop_token(res_op_array TSRMLS_CC);
    znode *value= oel_stack_pop_operand(res_op_array TSRMLS_CC);
    if (arg_ref) value->u.EA.type |= ZEND_PARSED_REFERENCE_VARIABLE;
    znode *key;
    if (oel_stack_size_operand(res_op_array TSRMLS_CC) > 0) {
        if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_VARIABLE)) oel_compile_error(E_ERROR, "oel_add_begin_foreach_body without oel_add_begin_variable_parse (for key)");
        else oel_stack_pop_token(res_op_array TSRMLS_CC);
        key= oel_stack_pop_operand(res_op_array TSRMLS_CC);
    } else {
        key= oel_create_extvar(res_op_array TSRMLS_CC);
        key->op_type= IS_UNUSED;
    }
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_FOREACH_BODY)) oel_compile_error(E_ERROR, "token is not of type foreach body");
    znode *open_brackets_token= oel_stack_pop_token(res_op_array TSRMLS_CC);
    znode *as_token=            oel_stack_pop_token(res_op_array TSRMLS_CC);
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_FOREACH)) oel_compile_error(E_ERROR, "token is not of type foreach");
    znode *foreach_token=       oel_stack_top_token(res_op_array TSRMLS_CC);
    oel_stack_push_token(res_op_array, as_token TSRMLS_CC);

    php_oel_saved_env *env= oel_env_prepare(res_op_array TSRMLS_CC);
    if (key->op_type != IS_UNUSED) zend_check_writable_variable(key);
    zend_check_writable_variable(value);
    zend_do_foreach_cont(foreach_token, open_brackets_token, as_token, value, key TSRMLS_CC);
    oel_env_restore(res_op_array, env TSRMLS_CC);
}

PHP_FUNCTION(oel_add_end_foreach) {
    zval *arg_op_array;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &arg_op_array) == FAILURE) { RETURN_NULL(); }
    php_oel_op_array *res_op_array= oel_fetch_op_array(arg_op_array TSRMLS_DC);

    znode *as_token=            oel_stack_pop_token(res_op_array TSRMLS_CC);
    if (!oel_token_isa(res_op_array TSRMLS_CC, 1, OEL_TYPE_TOKEN_FOREACH)) oel_compile_error(E_ERROR, "token is not of type foreach");
    znode *foreach_token=       oel_stack_pop_token(res_op_array TSRMLS_CC);

    php_oel_saved_env *env= oel_env_prepare(res_op_array TSRMLS_CC);
    zend_do_foreach_end(foreach_token, as_token TSRMLS_CC);
    oel_env_restore(res_op_array, env TSRMLS_CC);
}