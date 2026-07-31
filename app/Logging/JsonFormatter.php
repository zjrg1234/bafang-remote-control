<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Logging;

use Illuminate\Support\Facades\Config;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;
use Throwable;

/**
 * Encodes whatever record data is passed to it as json
 *
 * This can be useful to log to databases or remote APIs
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class JsonFormatter extends NormalizerFormatter
{
    public const BATCH_MODE_JSON = 1;
    public const BATCH_MODE_NEWLINES = 2;

    protected $batchMode;
    protected $appendNewline;
    protected $ignoreEmptyContextAndExtra;

    protected $source = 'fpm';
    protected $requestId = '';
    protected $uri = '';
    protected $clientIp = '';

    /**
     * @var bool
     */
    protected $includeStacktraces = false;
//
//    public function __construct(?string $dateFormat = null)
//    {
//        $this->appendNewline = true;
////        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;
//        $this->source = php_sapi_name();
//        $this->requestId = Config::get('requestId');
//        if ('cli' != $this->source) {
//            $this->uri = $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'];
//            $this->clientIp = getIpRaw();
//        }
//    }


    /**
//     * {@inheritdoc}
//     *
//     * @suppress PhanTypeComparisonToArray
//     */
    public function format(LogRecord $record): string // 参数改为 LogRecord，返回值保留 string
    {
        // 1. 调用父类 format 方法，将 LogRecord 转为可修改的数组
        $formatted = parent::format($record);

        // 2. 你的自定义逻辑（比如追加 traceId、格式化时间等）
        // 示例：追加 traceId
        $this->requestId = Config::get('requestId');
        $formatted['traceId'] = $this->requestId;
        // 示例：格式化时间（从 LogRecord 的 datetime 属性读取，而非数组）
        $formatted['datetime'] = $record->datetime->format('Y-m-d H:i:s.u');

        // 3. 移除可能触发异常的 @timestamp 字段（可选，避免之前的 LogicException）
        unset($formatted['@timestamp']);
        // 4. 转为 JSON 字符串返回
        return json_encode($formatted, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

}
