<?php


/*
 * ���õ���ģʽ����ֹ������XML
 */
include './Public/Conf/config.php';
header('Content-Type: text/html; charset=GBK');
error_reporting(0);
	
$Printer = NetPrinterClass::getInstance(); 


if (isset($Printer->params['id']) && isset($Printer->params['sta']))  // ���ش�ӡ���
{
    /*
     * @todo 
     */
    if ($_GET['sta'] == 0) {
    	$sqlsta = "UPDATE ".DB_PREFIX."orders SET status='�Ѵ�ӡ' WHERE orderid=\"$_GET[id]\"";
    	mysql_query('set names gb2312');
		mysql_query($sqlsta);
    }
}
else // ������Ҫ��ӡ������
{   
	$time = date('Y/m/d').' 08:00:00';
	$sql = "SELECT * FROM ".DB_PREFIX."orders WHERE status='δ��ӡ' AND time>\"$time\"";
	mysql_query('set names gb2312');
	$query = mysql_query($sql);
	
	while ( $result = mysql_fetch_array($query) ) {
		$orderid = $result['orderid'];
		$sqldetail = "SELECT * FROM ".DB_PREFIX."orders_detail WHERE orderid=\"$orderid\"";
		// print_r($sqldetail);
		mysql_query('set names gb2312');
		$querydetail = mysql_query($sqldetail);
		while ($row = mysql_fetch_array($querydetail)) {
			// print_r($row);
			$title = $row['title'];
			$price = $row['price'];
			$num = $row['quantity'];

			$detail .= $title.'      '.$price.'      '.$num.'%%';
		}
		// print_r($detail);

		$orderid = $result['orderid'];
		$feiyindetail = $detail;
		$note = $result['note'];
		$totalprice = $result['totalprice'];
		$pay_status = $result['pay_status'];
		$uid = $result['uid'];

		$sqlurl = "SELECT * FROM ".DB_PREFIX."users WHERE uid=\"$uid\"";
		mysql_query('set names gb2312');
		$user = mysql_fetch_array(mysql_query($sqlurl));
		$username = $user['username'];
		$address = $user['address'];
		$phone = $user['phone'];
		$time = $result['time'];
	}

	$content = '֣���˿�����ӭ������

	������ţ�'.$orderid.'

	��Ŀ      ���ۣ�Ԫ��    ����
	------------------------------
	'.$feiyindetail.'

	��ע��'.$note.'
	------------------------------
	�ϼƣ�'.$totalprice.'Ԫ 
	����״̬��'.$pay_status.'

	��ϵ�û���'.$username.'
	�ͻ���ַ��'.$address.'
	��ϵ�绰��'.$phone.'
	����ʱ�䣺'.$time.'';//�������

	$time = date("Y-m-d H:i:s");
	if ( $orderid ) {
	    echo $Printer->setId( $orderid ) // ����ID
	            ->setTime( strtotime( $time ) ) // ����ʱ��
	            ->setContent( $content ) // ����content
	            ->setSetting("101:6|105:0") // ���ô�ӡ�����������ݣ�����ο�Э�鲿���ļ�������Ǳ�Ҫ��Ҫ���ã�Ҳ����Ϊ��
	            ->display(); // ���
	}
}


class NetPrinterClass {
    
    private static $_instance;
    
    private $time;
    private $content = "";
    private $setting = "";
    private $id = "";
    
    public $params = array();
    
    private function __construct() {
        $this->getParams();        
        $this->time = date('Y-m-d H:i:s');
    }
    
    private function __clone() {}  //����__clone()��������ֹ��¡    
    
    public static function getInstance()    
    {   
        if(! (self::$_instance instanceof self) ) {    
            self::$_instance = new self();    
        }    
        
        /*
         * ��֤�Ƿ�Ϊ��������
         */
        /*
        if (!isset(self::$_instance->params['usr'])
                && !isset(self::$_instance->params['sgn'])
                && md5(self::$_instance->params['usr']) != self::$_instance->params['sgn'])
        {
            return false;
        }
        */
        return self::$_instance;    
    }    
    /*
     * ��ӡ�ն�����ƽ̨�·�����
     * 
     */
    /**
      +----------------------------------------------------------
     * ����ʱ�� ʱ�䲻��С��2013-08-01 00:00:00 ͬʱ ʱ�䲻���ڴ���2030-08-01 00:00:00
      +----------------------------------------------------------
     * @param string $timestamp ʱ���
      +----------------------------------------------------------
     */
    public function setTime( $timestamp )
    {
        if ($timestamp > 1375315200 && $timestamp < 1911772800) {
            $this->time = date('Y-m-d H:i:s', $timestamp);
        }
        return $this;
    }
    
    
    /**
      +----------------------------------------------------------
     * д������
      +----------------------------------------------------------
     * @param string $content ����
      +----------------------------------------------------------
     */
    public function setContent( $content )
    {
        $this->content = strip_tags($content);
        return $this;
    }

    /**
      +----------------------------------------------------------
     * ���ô�ӡ������
      +----------------------------------------------------------
     * @param array $setting ���� key(��Ӧ��) => value(����)
      +----------------------------------------------------------
     */
    public function setSetting( $setting )  
    {
        if (!empty($setting) && is_array($setting)) {
            $this->setting = "";
            foreach ($setting as $k => $v) {
                if (is_numeric($k)) 
                {
                    $this->setting .= $k.":".strip_tags($v)."|";
                }
            }
        }
        else
        {
            $this->setting = strip_tags($setting);
        }
        return $this;
    }

    /**
      +----------------------------------------------------------
     * ����ID
      +----------------------------------------------------------
     * @param string $id id SYD123456789
      +----------------------------------------------------------
     */
    public function setId( $id )  
    {
        $this->id = strip_tags($id);        
        return $this;
    }
    
    
    /**
      +----------------------------------------------------------
     * ���������Ƿ����������ݳ��� ���ܶ���2000�ֽ�
      +----------------------------------------------------------
     * @return boolean  
      +----------------------------------------------------------
     */
    public function maxLength($str, $length = 2000)
    {
        if (mb_strlen($str) > 2000) 
        {
            return false;
        }
        return true;
    }

    /**
      +----------------------------------------------------------
     * ���ɴ�����XML ���ܶ���2000�ֽ�
      +----------------------------------------------------------
     * @return string xml 
      +----------------------------------------------------------
     */
    public function display() 
    {
        
        $xml = '<?xml version="1.0" encoding="GBK"?>';
        $xml .= "<r>";
        
        $xml .= "<id>".$this->id."</id>";
        $xml .= "<time>".$this->time."</time>";
        $xml .= "<content>".$this->content."</content>";
        $xml .= "<setting>".$this->setting."</setting>";
        
        $xml .= "</r>";
        
        if ($this->maxLength($xml)) {
            header("Content-type: text/xml");
            return $xml;
        }
        return false;
    }
    
    
     /**
      +----------------------------------------------------------
     * �������ز���
      +----------------------------------------------------------
     * @return array  
      +----------------------------------------------------------
     */
    public function getParams() 
    {
        $arr = array();
        
        if (isset($_REQUEST['usr'])) $arr['usr'] = $_REQUEST['usr']; // �û�IMEI����
        if (isset($_REQUEST['ord'])) $arr['ord'] = $_REQUEST['ord']; // ���ν��׵����кţ������ظ�
        if (isset($_REQUEST['sgn'])) $arr['sgn'] = $_REQUEST['sgn']; // ����ǩ���� MD5(usr)ת��д
        
        if (isset($_REQUEST['id'])) $arr['id'] = $_REQUEST['id']; // ƽ̨�·���ӡ���ݵ�ID��
        if (isset($_REQUEST['sta'])) $arr['sta'] = $_REQUEST['sta']; // ��ӡ��״̬��0Ϊ��ӡ�ɹ��� 1Ϊ���ȣ�3Ϊȱֽ��ֽ�ȣ�
        
        $this->params = $arr;
        
        return $arr;
    }
}
?>
