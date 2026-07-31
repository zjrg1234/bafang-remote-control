<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemManager;
use OSS\OssClient;

class OssStorageServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     *
     * @return void
     */
    public function register()
    {
        // 扩展文件系统，注册 oss 驱动
        $this->app->extend('filesystem', function ($app, $manager) {
            /** @var FilesystemManager $manager */
            $manager->extend('oss', function ($app, $config) {
                $ossClient = new OssClient(
                    $config['access_key'],
                    $config['secret_key'],
                    $config['endpoint'],
                    $config['is_cname']
                );
                // 返回 Laravel 兼容的 OSS 存储实例（简化版，可直接使用）
                return new \Illuminate\Filesystem\Filesystem(new class($ossClient, $config) {
                    protected $ossClient;
                    protected $config;

                    public function __construct(OssClient $ossClient, array $config)
                    {
                        $this->ossClient = $ossClient;
                        $this->config = $config;
                    }

                    // 实现文件上传核心方法
                    public function put($path, $contents, $options = [])
                    {
                        $bucket = $this->config['bucket'];
                        // 处理文件内容（支持字符串/资源流）
                        $contents = is_resource($contents) ? $contents : (string)$contents;
                        // 上传文件到 OSS
                        $this->ossClient->putObject($bucket, $path, $contents, $options);
                        // 返回 OSS 文件路径（可拼接完整 URL）
                        return $this->getUrl($path);
                    }

                    // 获取文件完整 URL
                    public function getUrl($path)
                    {
                        $scheme = $this->config['ssl'] ? 'https' : 'http';
                        return "{$scheme}://{$this->config['bucket']}.{$this->config['endpoint']}/{$path}";
                    }

                    // 可按需扩展其他方法（delete、exists 等）
                    public function delete($path)
                    {
                        $this->ossClient->deleteObject($this->config['bucket'], $path);
                        return true;
                    }

                    public function exists($path)
                    {
                        return $this->ossClient->doesObjectExist($this->config['bucket'], $path);
                    }
                });
            });
            return $manager;
        });
    }
}
