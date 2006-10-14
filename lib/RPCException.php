<?
class RPCException extends Exception {
	public $fileRPC;
	public $lineRPC;
	function __construct($msg,$func=null,$shift=1,$code=0,$fileRPC=null,$lineRPC=null){
        parent::__construct($msg,$func,$shift,$code);
        if(!is_null($fileRPC)) 
        	$this->fileRPC=$fileRPC;
        else
        	$this->fileRPC=$this->getFile();
        		
        if(!is_null($lineRPC)) 
        	$this->lineRPC=$lineRPC;
        else
        	$this->lineRPC=$this->getLine();	
        return; 
    }
    function getMyFile(){ return $this->fileRPC; }
    function getMyLine(){ return $this->lineRPC; }
}