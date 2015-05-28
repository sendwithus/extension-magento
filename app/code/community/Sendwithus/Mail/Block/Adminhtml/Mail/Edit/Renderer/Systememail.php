<?php

class Sendwithus_Mail_Block_Adminhtml_Mail_Edit_Renderer_Systememail
extends Mage_Adminhtml_Block_Widget
implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * renderer
     *
     * @param Varien_Data_Form_Element_Abstract $element
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		$html = "";
		$values = $element->getData("values");
        $html = '<tr><td>' . $element->getLabelHtml(). '</td>';
        		// add checkbox
		$html .= '<td class="value" style="width:80%">'.
			'<input id="checkbox_'.$values["id"].'" type="checkbox" tabindex="1" onchange="" onclick="" '.
				($values["checked"]?'checked="checked"':"").//'"'.
			' value="" name="checkbox_'.$values["id"].'"></input>';
		// add select box
		$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$html .= '<select id="select_'.$values["id"].'" class=" required-entry select" tabindex="1" readonly="" onchange="" onclick="" name="select_'.$values["id"].'">';
		foreach ($values["available"] as $k=>$v){
			$html .= '<option value="'.$k.'" '.($k==$values["selected"]?"selected":"").'>'.$v.'</option>';
		}
			//hardcode in a fake entry that allows us to block transactional emails using the same plugin
			$html .= '<option value="'.Sendwithus_Mail_Model_Template::DO_NOT_SEND.'" '.($values["selected"]==Sendwithus_Mail_Model_Template::DO_NOT_SEND?"selected":"").'>-- Do Not Send --</option>';
		$html .= '</select></td></tr>';
        return $html;
    }
}
