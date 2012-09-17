<?php
return
array (
  'Scope' => 'prototype',
  'PostConstruct' => 'onInit',
  'PreDestroy' => 'onEnd',
  'Inject' =>
  array (
    'setter' =>
    array (
      0 =>
      array (
        'setDb' =>
        array (
          0 =>
          array (
            'pos' => 0,
            'interface' => 'Aura\\Di\\Db',
            'param_name' => 'db',
            'named' => NULL,
          ),
        ),
      ),
      1 =>
      array (
        'setUserDb' =>
        array (
          0 =>
          array (
            'pos' => 0,
            'interface' => 'Aura\\Di\\Db',
            'param_name' => 'db',
            'named' => NULL,
          ),
        ),
      ),
      2 =>
      array (
        'setAdminDb' =>
        array (
          0 =>
          array (
            'pos' => 0,
            'interface' => 'Aura\\Di\\Db',
            'param_name' => 'db',
            'named' => 'stage_db',
          ),
        ),
      ),
      3 =>
      array (
        'setDouble' =>
        array (
          0 =>
          array (
            'pos' => 0,
            'interface' => 'Aura\\Di\\User',
            'param_name' => 'user',
            'named' => 'admin_user',
          ),
          1 =>
          array (
            'pos' => 1,
            'interface' => 'Aura\\Di\\Db',
            'param_name' => 'db',
            'named' => 'production_db',
          ),
        ),
      ),
    ),
  ),
);
