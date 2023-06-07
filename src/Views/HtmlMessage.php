<?php

use WpMailCatcher\GeneralHelper;

echo GeneralHelper::filterHtml($log['message'] ?? '');
