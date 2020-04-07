<?PHP
define('SLASH','/');

class snclip {

	private $_from = false;
	private $_to   = false;
	private $_stored = false;
	private function _scan($path){
		$result = [];
		foreach(scandir($path) as $content) ($content !='.' && $content !='..') ? $result[] = $content : '';
		return $result;
	}

	public function from($path){
		if(!is_dir($path)) return $this;
		$this->_from = $path;
		return $this;
	}

	public function to($path){
                if(!is_dir($path)) return $this;
                $this->_to = $path;
                return $this;
        }

	public function scan(){
		if(!$this->_from || !$this->_to) die(' ! ERROR: El directorio base o destino no existen' . PHP_EOL);
		$path = $this->_from;
		$years = $this->_scan($path);
		foreach($years as $year){
                        $places = $this->_scan($path . SLASH . $year);
                        foreach($places as $place){
                                $events =$this->_scan($path . SLASH . $year . SLASH . $place);
                                foreach($events as $event){
                                        $this->_store($year,$place,$event);
                                }
                        }
                }
		return $this;
	}



	private function _store($year,$place,$event){
		$dir = $this->_from . SLASH . $year . SLASH . $place . SLASH . $event ; 
		$useBase = false;
		if (!is_dir($dir . SLASH . 'orig') ) {
			$useBase = true;
		}else{
			$useBase = false;
			$dir =  $dir . SLASH . 'orig';
		}
		$files = $this->_scan($dir);	
		$selected = [];
		foreach($files as $file){
			$r = explode('.',$file);
			$ext = strtolower($r[count($r)-1]);
			if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp') $selected[]=$file;
		}
		$this->_stored[$year][$place][$event] =[
			'files'=> $selected,
			'orig' => (!$useBase) ? true:false
		];
		return $this;
	}

	public function export(){
		if($this->_stored != false) return json_encode($this->_stored,JSON_PRETTY_PRINT);
	}

}


$snclip = new snclip;

if( !isset($argv[1]) || !isset($argv[2])  ) die(' ~ MODO DE USO: php snclip.php <directorio_base> <directorio_destino>' . PHP_EOL);

$baseDir = $argv[1];
$destDir = $argv[2];
if(!is_dir($baseDir)) die('Error: El directorio base debe ser un directorio' . PHP_EOL);
if(!is_dir($destDir)) die('Error: El directorio destino debe ser un directorio' . PHP_EOL);

echo $snclip->from($baseDir)->to($destDir)->scan()->export();


