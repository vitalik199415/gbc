 <?php
class Mlogin extends AG_Model
{
	const USERS = 'users';
    const M_ADMIN = 'm_administrators';
	
	function __construct()
		{
			parent::__construct();
		}
		
	public function isAutorize()
		{
			if($this->session->get_data('id_users') && $this->session->get_data('id_users')>0)
			{
				return true;
			}
			return false;		
		}
	public function autorize($data)
		{
			$query = $this->db->select("MA.*, U.`rang`")->from(self::M_ADMIN." as MA")
                          ->join(self::USERS.' as U', 'U.`id_users`=MA.`id_users`', 'INNER')->where_in($data);
			$result = $this->db->get()->result_array();
			if(count($result)==1)
				{
					$this->session->set_data('id_users',$result[0]['id_users']);
					$this->session->set_data('rang', $result[0]['rang']);
					
                    if($result[0]['superadmin'] == 1){
                        $this->session->set_data('super', $result[0]['superadmin']);
                    }
					return true;
				}
			return false;	
		}		
}
?>