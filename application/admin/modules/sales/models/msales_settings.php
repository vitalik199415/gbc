<?php
class Msales_settings extends AG_Model
{
	const S_ALIAS 		= 'm_orders_settings_alias';
	const ID_S_ALIAS 	= 'id_m_orders_settings_alias';
	const S_VALUES 		= 'm_orders_settings_value';
	
	protected $default_settings = array(
		'mail_' => array(
			'new_order_email' => 'support@gbc.net.ua',
			'shop_name' => 'SHOP',
			'send_confirmed' => 1
		),
		'settings_' => array(
			'order_processing_type' => 'full'
		)
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('users/musers');
		$user = $this->musers->get_user();
		$this->default_settings['mail_']['new_order_email'] = $user['email'];
	}
	
	public function edit()
	{
		$this->load->helper('sales/sales_settings_helper');
		$data['settings'] = $this->get_sales_settings();
		helper_sales_settings_form_build($data);
	}
	
	public function get_sales_settings()
	{
		$settings_array = $this->get_default_settings();
		$query = $this->db
				->select("A.`".self::ID_S_ALIAS."` AS ID, A.`prefix`, A.`alias`, CONCAT(A.`prefix`, A.`alias`) AS settings, B.`value`")
				->from("`".self::S_ALIAS."` AS A")
				->join("`".self::S_VALUES."` AS B",
						"B.`".self::ID_S_ALIAS."` = A.`".self::ID_S_ALIAS."`",
						"INNER")
				->where("B.`".self::ID_USERS."`", $this->id_users);
		foreach($query->get()->result_array() as $ms)
		{
			$settings_array[$ms['settings']] = $ms['value'];
		}
		return $settings_array;
	}
	
	public function get_default_settings($s_key = FALSE)
	{
		$default_settings = array();
		if($s_key)
		{
			$settings_array = $this->default_settings[$s_key];
			foreach($settings_array as $key => $ms)
			{
				$default_settings[$s_key.$key] = $ms;
			}
			return $default_settings;
		}
		else
		{
			$settings_array = $this->default_settings;
			foreach($settings_array as $key => $ms)
			{
				foreach($ms as $key1 => $ms1)
				{
					$default_settings[$key.$key1] = $ms1;
				}
			}
			return $default_settings;
		}
	}
	
	public function save()
	{
		$settings_alias = $this->default_settings;; 
		$post = $this->input->post('settings');
		$query = $this->db
				->select("A.`".self::ID_S_ALIAS."` AS ID, A.`prefix`, A.`alias`, CONCAT(A.`prefix`, A.`alias`) AS settings, B.`value`")
				->from("`".self::S_ALIAS."` AS A")
				->join("`".self::S_VALUES."` AS B",
						"B.`".self::ID_S_ALIAS."` = A.`".self::ID_S_ALIAS."` && B.`".self::ID_USERS."` = ".$this->id_users,
						"LEFT");
		$this->db->trans_start();
		foreach($query->get()->result_array() as $ms)
		{
			if(isset($post[$ms['settings']]))
			{
				$settings_alias[$ms['settings']] = array(self::ID_S_ALIAS => $ms['ID'], 'value' => $post[$ms['settings']]);
				if($ms['value'] == NULL)
				{
					$this->sql_add_data($settings_alias[$ms['settings']])->sql_using_user()->sql_save(self::S_VALUES);
				}
				else
				{
					$this->sql_add_data($settings_alias[$ms['settings']])->sql_using_user()->sql_save(self::S_VALUES, array(self::ID_S_ALIAS => $settings_alias[$ms['settings']][self::ID_S_ALIAS]));
				}
			}
		}
		
		$this->db->trans_complete();
		if($this->db->trans_status()) 
		{
			return TRUE; 
		}
		return FALSE;
	}
}
?>