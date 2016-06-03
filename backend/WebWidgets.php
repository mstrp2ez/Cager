<?php

include_once('backend/mysqlfunctions.php');
include_once('backend/Log.php');

class SelectSearch {
	
	private $m_Caption;
	private $m_Table;
	private $m_WidgetID;
	private $m_Params;
	
	public function __construct($p_Caption,$p_Table,$p_Params=null){
		$this->m_Caption=$p_Caption;
		$this->m_Table=$p_Table;
		$this->m_WidgetID=uniqid();
		$this->m_Params=$p_Params;
	}
	public function WidgetID(){return $this->m_WidgetID;}
	public function Caption(){return $this->m_Caption;}
	public function display(array $p_Params,$p_NoJQUI=false/* $p_Field,array $p_Properties, $p_Default='' */){
		if(count($p_Params)<=0){$p_Params=$this->m_Params;}
		if($p_Params==null){
			Log::logAdv(array('Invalid parameters (SelectSearch): ', $p_Params));
			return false;
		}
		$field=(isset($p_Params['field']))?$p_Params['field']:'';
		$data=MysqlShort::Select($field,$this->m_Table,(isset($p_Params['properties']))?$p_Params['properties']:array());
		if($data===false){
			echo '<span class="web-widget-error">No data</span>';
			return false;
		}
		if($p_NoJQUI===false){
		?>
		<div class="property-portlet" id="widget_<?php echo $this->m_WidgetID; ?>">
			<div class="property-portlet-header"><?php echo $this->m_Caption; ?></div>
			<div class="property-portlet-content">
				<div class="web-widget-search-wrap">
					<select style="width:160px;" id="select_<?php echo $this->m_WidgetID;?>" class="web-widget-select-list">
						<?php 
							foreach($data as $item){
								echo '<option class="web-widget-option">' . $item[$field] . '</option>';
							}
						?>
					</select>
					<input type="text" class="web-widget-search-box" value="<?php echo (isset($p_Params['default']))?$p_Params['default']:'';?>" id="input_<?php echo $this->m_WidgetID; ?>">
				</div>
			</div>
		</div>
		<?php
		}else{
			?>
			<div class="web-widget-search-wrap">
					<select style="width:160px;" id="select_<?php echo $this->m_WidgetID;?>" class="web-widget-select-list">
						<?php 
							foreach($data as $item){
								echo '<option class="web-widget-option">' . $item[$field] . '</option>';
							}
						?>
					</select>
					<input type="text" class="web-widget-search-box" value="<?php echo (isset($p_Params['default']))?$p_Params['default']:'';?>" id="input_<?php echo $this->m_WidgetID; ?>">
				</div>
			<?php
		}
	}
}

class NumericSpinner{
	private $m_Caption;
	private $m_WidgetID;
	private $m_Type;
	
	public function __construct($p_Caption,$p_Type="float"){
		$this->m_Caption=$p_Caption;
		$this->m_WidgetID=uniqid();
		$this->m_Type=$p_Type;
	}
	public function WidgetID(){return $this->m_WidgetID;}
	public function display($p_Default=0,$p_NoJQUI=false){
	
		if($p_NoJQUI===false){
		?>
		<div class="property-portlet" id="widget_<?php echo $this->m_WidgetID; ?>">
			<div class="property-portlet-header"><?php echo $this->m_Caption; ?></div>
			<div class="property-portlet-content">
				<label for="<?php echo $this->m_WidgetID; ?>"><?php echo $this->m_Caption; ?></label>
				<input class="spinner type-<?php echo $this->m_Type;?>" size="4" value="<?php echo $p_Default;?>" name="<?php echo $this->m_Caption; ?>">
			</div>
		</div>
		<?php
		}else{
		?>
		<div class="numeric-spinner" id="widget_<?php echo $this->m_WidgetID; ?>">
			<div class="numeric-spinner-header ui-widget ui-widget-header"><?php echo $this->m_Caption; ?></div>
			<div class="numeric-spinner-content">
				<input class="spinner type-<?php echo $this->m_Type;?>" size="4" value="<?php echo $p_Default;?>" name="<?php echo $this->m_Caption; ?>">
			</div>
		</div>
		<?php
		}
		
	}
}

?>