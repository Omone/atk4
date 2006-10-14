<?
class RPCServer extends AbstractController {
    /*
     * If you want your API to receive RPC calls, you have to initialize this class
     * and set handler class and key. Everything else will be performed automatically.
     */

    var $handler;
    var $security_key=null;

	private $allowed_ip = array();

    function setSecurityKey($key){
        $this->security_key=$key;
        return $this;
    }
    function setHandler($class_name){
        try{
	    	if (count($this->allowed_ip)) {
	    		if (!in_array($_SERVER['REMOTE_ADDR'],$this->allowed_ip))
		            $this->_error_and_exit('Your IP not in allowed list',0,__FILE__,__LINE__);
	    			
	    	}
        	
            if(is_object($class_name)){
                $this->handler=$class_name;
            }else{
                $this->handler=$this->add($class_name);
            }
            if(!isset($_POST['data']))
		        $this->_error_and_exit('No "data" specified in POST',0,__FILE__,__LINE__);

            @$data = unserialize(stripslashes($_POST['data']));
            if($data===false || !is_array($data))
		        $this->_error_and_exit('Data was received, but was corrupted',0,__FILE__,__LINE__);

            if($this->security_key && count($data)!=3){
		        $this->_error_and_exit('This handler requires security key',0,__FILE__,__LINE__);
            }

            if(!$this->security_key && count($data)==3){
		        $this->_error_and_exit('Key was specified but is not required',0,__FILE__,__LINE__);
            }

            if(count($data)==3){
                list($method,$args,$checksum)=$data;

                $rechecksum=md5(serialize(array($method,$args,$this->security_key)));
                if($rechecksum!=$checksum)
		        	$this->_error_and_exit('Specified security key was not correct',0,__FILE__,__LINE__);
            }else{
                list($method,$args)=$data;
            }

            $result = call_user_func_array(array($this->handler,$method),$args);

            echo 'AMRPC'.serialize($result);
        }
        catch(BaseException $e){
            // safe send any of type exceptions (remove nested objects from exception)
            $this->_error_and_exit($e->getMessage(),$e->getCode(),$e->getMyFile(),$e->getMyLine());
        }
        catch(Exception $e){
            // safe send any of type exceptions (remove nested objects from exception)
            $this->_error_and_exit($e->getMessage(),$e->getCode(),$e->getMyFile(),$e->getMyLine());
            
            //echo 'AMRPC'.preg_replace('/;O:\d+:".+?"/smi',';a',serialize($e));
        }
    }
    
    private function _error_and_exit($message,$code,$file,$line) {
    	echo 'ERRRPC'.serialize(array(
				'message'=>$e->getMessage(),
				'code'=>$e->getCode(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()));
		exit;		
    }
    
    function setAllowedIP($list_of_ips = array()) {
    	$this->allowed_ip = $list_of_ips;
    }
    
}
