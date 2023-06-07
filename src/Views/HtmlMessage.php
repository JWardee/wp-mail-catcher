<?php

use WpMailCatcher\GeneralHelper;

echo GeneralHelper::unfilterHtml($log['message'] ?? '');
