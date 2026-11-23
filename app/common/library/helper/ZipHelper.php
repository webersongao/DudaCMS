<?php
namespace app\common\library\helper;

use DirectoryIterator;
use Exception;
use ZipArchive;

class ZipHelper
{
    protected $zip;
    protected $root;
    protected $ignored_names;
    public function __construct(){
        $this->zip = new ZipArchive;
    }
    /**
     * 解压zip文件到指定文件夹
     *
     * @access public
     * @param string $zipFile 压缩文件路径
     * @param string $path  压缩包解压到的目标路径
     * @return bool 解压成功返回 true 否则返回 false
     */
    public function unzip (string $zipFile,string $path): bool
    {
        if ($this->zip->open($zipFile) === true) {
            $this->zip->close();
            // 统一走安全解压，防止 Zip Slip
            return self::extractSafely($zipFile, $path);
        }
        return false;
    }

    /**
     * 创建压缩文件
     * @access public
     * @param string $zipFile 将要生成的压缩文件路径
     * @param string $folder 将要被压缩的文件夹路径
     * @param null $ignored 要忽略的文件列表
     * @return bool 压缩包生成成功返回true 否则返回 false
     * @throws Exception
     */
    public function zip (string $zipFile,string $folder, $ignored = null): bool
    {
        if(!is_dir(dirname($zipFile)))
        {
            FileHelper::mkDirs(dirname($zipFile));
        }

        if (!is_file($zipFile)) {
            $fp = fopen($zipFile, 'w');
            fclose($fp);
        }
        $this->ignored_names = is_array($ignored) ? $ignored : ($ignored ? array($ignored) : array());
        if ($this->zip->open($zipFile, ZipArchive::OVERWRITE) !== true) {
            throw new Exception("cannot open <$zipFile>\n");
        }
        $folder = substr($folder, -1) == '/' ? substr($folder, 0, strlen($folder)-1) : $folder;
        if(strstr($folder, '/')) {
            $this->root = substr($folder, 0, strrpos($folder, '/')+1);
            $folder = substr($folder, strrpos($folder, '/')+1);
        }
        $this->createZip($folder);
        return $this->zip->close();
    }

    /**
     * 递归添加文件到压缩包
     *
     * @access private
     * @param string $folder 添加到压缩包的文件夹路径
     * @param string|null $parent 添加到压缩包的文件夹上级路径
     * @return void
     */
    private function createZip (string $folder,?string $parent = null)
    {
        $full_path = $this->root . $parent . $folder;
        $zip_path = $parent . $folder;
        $this->zip->addEmptyDir($zip_path);
        $dir = new DirectoryIterator($full_path);
        foreach($dir as $file) {
            if(!$file->isDot()) {
                $filename = $file->getFilename();
                if(!in_array($filename, $this->ignored_names)) {
                    if($file->isDir()) {
                        $this->createZip($filename, $zip_path.'/');
                    }else {
                        //第二个参数是重命名文件名,带上路径就可以改变当前文件在压缩包里面的路径.
                        $this->zip->addFile($full_path.'/'.$filename, $zip_path.'/'.$filename);
                    }
                }
            }
        }
    }

    /**
     * 读取压缩包文件与目录列表
     *
     * @access public
     * @param string $zipFile 压缩包文件
     * @return array 文件与目录列表
     */
    public function fileList(string $zipFile): array
    {
        $file_dir_list = array();
        $file_list = array();
        if ($this->zip->open($zipFile) === true) {
            for ($i = 0; $i < $this->zip->numFiles; $i++) {
                $numFiles = $this->zip->getNameIndex($i);
                if (preg_match('/\/$/i', $numFiles))
                {
                    $file_dir_list[] = $numFiles;
                }
                else
                {
                    $file_list[] = $numFiles;
                }
            }
        }
        return array('files'=>$file_list, 'dirs'=>$file_dir_list);
    }

    /**
     * 得到文件头与文件类型映射表
     * @param $bin string 文件的二进制前一段字符
     * @return bool
     */
    private function getTypeList (string $bin): bool
    {
        $array = array(
            array("504B0304", "zip")
        );
        foreach ($array as $v)
        {
            $been = strlen(pack("H*", $v[0])); //得到文件头标记字节数
            $bin = substr($bin, 0, intval($been)); ///需要比较文件头长度
            if(strtolower($v[0]) == strtolower(array_shift(unpack("H*", $bin))))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 安全解压：拒绝 ../、绝对路径、盘符、空字节与符号链接，防止 Zip Slip
     */
    public static function extractSafely(string $zipFile, string $target): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            return false;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!self::isSafeEntry($name)) {
                $zip->close();
                return false;
            }

            // 拒绝符号链接（Unix external attributes）
            $opsys = 0;
            $attr  = 0;
            if ($zip->getExternalAttributesIndex($i, $opsys, $attr)) {
                if ((($attr >> 16) & 0170000) === 0120000) {
                    $zip->close();
                    return false;
                }
            }
        }

        $result = $zip->extractTo($target);
        $zip->close();
        return (bool) $result;
    }

    private static function isSafeEntry(string $name): bool
    {
        $name = str_replace('\\', '/', $name);
        if ($name === '' || strpos($name, "\0") !== false) {
            return false;
        }
        if ($name[0] === '/' || preg_match('#^[A-Za-z]:#', $name)) {
            return false;
        }
        return !preg_match('#(^|/)\.\.(/|$)#', $name);
    }
}