<?php
namespace Modules\LessonGaugeChart\Includes;

use Zabbix\Widgets\{
    CWidgetForm,
    CWidgetField
};

use Zabbix\Widgets\Fields\{
    CWidgetFieldTextBox,
    CWidgetFieldMultiSelectItem
};

class WidgetForm extends CWidgetForm
{
    public function addFields(): self
    {
        return $this
            ->addField(
                (new CWidgetFieldMultiSelectItem('itemid', _('Item')))
                    ->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
                    ->setMultiple(false)
            )
            ->addField(
                new CWidgetFieldTextBox('description', _('Description'))
            );
    }
}