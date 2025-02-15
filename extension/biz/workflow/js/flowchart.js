$(function()
{
    var $editModal = $('#editorElementModal');
    var $editModalBtn = $('#editorElementModalBtn');
    var $editorDeleteBtn = $('#editorDeleteBtn');
    var $editorCanvas = $('#editorCanvas');
    var editCallback;

    var showElementEditModal = function(position, element, callback)
    {
        var $window   = $(window);
        var width     = $editModal.outerWidth();
        var height    = $editModal.outerHeight();
        position.top  = Math.max(0, Math.min($window.height() - height, position.top));
        position.left = Math.max(0, Math.min($window.width()  - width,  position.left));
        $editModal.find('#elementName').val(element.name).parent().removeClass('has-error');
        $editModal.find('#elementCode').val(element.code).attr('readonly', element.codeReadonly ? 'readonly' : null).parent().removeClass('has-error');
        if($editModalBtn.data('zui.tooltip')) $editModalBtn.tooltip('destroy');
        editCallback = callback;
        $editModal.css(position).addClass('show');
        $editModal.find('#elementName').select().focus();
    };

    var isElementReadonly = function(element)
    {
        return element.type === 'start' || element.type === 'stop' || element.getData('readonly') ||(window.readonlyActions && window.readonlyActions.indexOf(element.getData('code')) > -1);
    };

    var handleElementActiveChange = function()
    {
        var activeElements = this.getActiveElements();
        var elementsCanDelete = activeElements.filter(function(ele)
        {
            return !isElementReadonly(ele) && (window.defaultActions.indexOf(ele.getData('code')) === -1 || !ele.data._saved);
        });
        $editorDeleteBtn.attr('disabled', elementsCanDelete.length ? null : 'disabled');
    };

    var getStartStopElements = function(flowchart)
    {
        flowchart = flowchart || getFlowchart();
        var start, stop;
        var nodeList = flowchart.nodeList;
        for(var i = 0; i < nodeList.length; ++i)
        {
            var node = nodeList[i];
            if(node.type === 'start') start = node;
            else if(node.type === 'stop') stop = node;
            if(start && stop) break;
        }
        return {start: start, stop: stop};
    };

    $editModal.on('input change', 'input', function()
    {
        $editModal.find('td.has-error').removeClass('has-error');
        if($editModalBtn.data('zui.tooltip')) $editModalBtn.tooltip('destroy');
    });
    $editModalBtn.on('click', function()
    {
        var $name = $editModal.find('#elementName');
        var $code = $editModal.find('#elementCode');
        var data = {name: $name.val(), code: $code.val()};
        var hasError;
        if(!data.name.length)
        {
            $name.parent().addClass('has-error');
            hasError = true;
        }
        if(!data.code.length)
        {
            $code.parent().addClass('has-error');
            hasError = true;
        }
        if(hasError)
        {
            if(!$editModalBtn.data('zui.tooltip'))
            {
                $editModalBtn.attr('data-toggle', 'tooltip').tooltip(
                {
                    show: true,
                    title: window.langNameAndCodeRequired,
                    tipClass: 'tooltip-danger',
                    placement: 'top',
                });
            }
            $editModalBtn.tooltip('show');
            return;
        }
        if(editCallback)
        {
            editCallback(data);
            editCallback = null;
        }
        $editModal.removeClass('show');
    });
    $(document).on('mouseup', function(e)
    {
        var $modal = $('#editorElementModal');
        if($modal.hasClass('show') && !$(e.target).closest('#editorElementModal').length)
        {
            editCallback = null;
            $modal.removeClass('show');
        }
    });

    $('.editor-element').on('mousedown', function()
    {
        $(this).tooltip('hide');
    })

    var canvasHeight  = $editorCanvas.height() || ($(window).height() - 145);
    flowchartData.forEach(function(data, index)
    {
        data.codeReadonly = true;

        if(window.isDefaultData)
        {
            // Adjust positions for default data
            if(data.id === 'start') data.position = {left: 40, top: 20};
            else if(data.id === 'stop') data.position = {left: 40, top: canvasHeight - 60};
            else data.position = {left: 30, top: 20 + index * 100};
        }
        else data._saved = true;
    });

    var flowChartOptions =
    {
        height: 'auto',
        data: flowchartData,
        autoLayoutDirection: 'vert',
        relationLineWidth: 2,
        padding: 20,
        relationLineColor: '#a6aab8',
        relationLineShape: 'polyline',
        defaultNodeType: 'process',
        relationArrowSize: 6,
        allowFreePorts: true,
        nodeMinWidth: 80,
        addFromDrop: '#editorElements',
        nodeHeight: 42,
        activeColor: '#1CA5FF',
        exportDetailPosition: false,
        centerOnCreate: 'horz',
        confirmRelationType: false,

        elementTypes:
        {
            start:
            {
                minWidth: 80,
                shapeStyle: {backgroundColor: '#b0bac1', borderColor: 'transparent'},
                textStyle: {color: '#fff'},
                text: window.flowchartElementTypes.start
            },
            process:
            {
                minWidth: 100,
                type: 'action',
                shapeStyle: {backgroundColor: '#40caca', borderColor: 'transparent'},
                textStyle: {color: '#fff'},
                text: window.flowchartElementTypes.process
            },
            decision:
            {
                type: 'diamond',
                style: {minWidth: 70, minHeight: 70},
                shapeStyle: {backgroundColor: '#fad43c', borderColor: 'transparent'},
                textStyle: {color: '#fff'},
                text: window.flowchartElementTypes.decision
            },
            result:
            {
                type: 'circle',
                shapeStyle: {backgroundColor: '#0964eb', borderColor: 'transparent', minWidth: 70, minHeight: 70},
                textStyle: {color: '#fff'},
                text: window.flowchartElementTypes.result
            },
            stop:
            {
                minWidth: 80,
                shapeStyle: {backgroundColor: '#b0bac1', borderColor: 'transparent'},
                textStyle: {color: '#fff'},
                text: window.flowchartElementTypes.stop
            },
        },
        showContextMenu: function(ele, items)
        {
            items.splice(0, 2);
            if(ele.id == 'start' || ele.id == 'stop' || ele.id == 'edit' || ele.id == 'create')
            {
                items.splice(1, 1);
            }
            return items;
        },
        onActiveElement: handleElementActiveChange,
        onUnactiveElement: handleElementActiveChange,
        onAddElement: function(elementsToAdd)
        {
            var flowchart = this;
            var elementsCanAdd = [];
            var startAndStop = getStartStopElements(flowchart);
            elementsToAdd.forEach(function(element)
            {
                var duplicatedStart = element.type === 'start' && startAndStop.start;
                var duplicatedStop = !duplicatedStart && element.type === 'stop' && startAndStop.stop;
                if(!duplicatedStart && !duplicatedStop)
                {
                    elementsCanAdd.push(element);
                }
                if(element.data && element.data['zui.tooltip']) delete element.data['zui.tooltip'];
            });
            if(elementsCanAdd.length < elementsToAdd.length)
            {
                bootbox.alert(window.langStartOrStopDuplicated);
            }
            return elementsCanAdd.length ? elementsCanAdd : false;
        },
        afterUpdateElement: function()
        {
            var startAndStop = getStartStopElements(this);
            var $editorElements = $('#editorElements');
            $editorElements.find('.editor-element-start')[startAndStop.start ? 'hide' : 'show']();
            $editorElements.find('.editor-element-stop')[startAndStop.stop ? 'hide' : 'show']();
            if(this.created) this.changed = true;
        },
        onChangeElementPosition: function()
        {
            if(this.created) this.changed = true;
        },
        deleteConfirm: function(ele, deleteElement)
        {
            window.bootbox.confirm(window.langConfirmToDelete.replace('%s', window.flowchartElementTypes[ele.type]), function(result)
            {
                if(result) deleteElement();
            });
        },
        onDeleteElement: function(elements)
        {
            handleElementActiveChange.call(this);
            elements.forEach(function(ele)
            {
                $('#tooltip-' + ele.id).remove();
            });
            if(this.created) this.changed = true;
        },
        doubleClickToEdit: function(element, flowchart, e)
        {
            var elementType = element.type;
            if(elementType === 'process' && !isElementReadonly(element))
            {
                showElementEditModal({left: e.pageX, top: e.pageY},
                {
                    name: element.getText(),
                    code: element.getData('code'),
                    codeReadonly: element.getData('codeReadonly'),
                }, function(newData)
                {
                    if(!element.codeReadonly) element.setData('code', newData.code);
                    element.setText(newData.name);
                    flowchart.render(element);
                    if(flowchart.created) flowchart.changed = true;
                });
                return true;
            }
            if(elementType === 'start' || elementType === 'stop') return true;
        }
    };
    var flowChart = $editorCanvas.flowChart(flowChartOptions).data('zui.flowChart');
    $editorDeleteBtn.on('click', function()
    {
        flowChart.tryDeleteElement(flowChart.getActiveElements()[0]);
        handleElementActiveChange.call(flowChart);
    });

    $('#saveBtn').on('click', saveFlowchart.bind(this, null, true));

    $('.nextStep, #editorSteps > ul > li > a[href*=ui]').on('click', function()
    {
        var href = $(this).attr('href');
        if(saveFlowchart(function(result)
        {
            if(result) setTimeout(function(){location.href = href;}, 1200);
        })) return false;
    });

    $('#elementCode').on('change', function()
    {
        var $input = $(this);
        $input.val($input.val().replace(/[^a-zA-Z]/g, '').toLowerCase());
    });

    $(window).on('beforeunload', function(e)
    {
        if(getFlowchart().changed)
        {
            e = e || window.event;

            // IE8, Firefox 4
            if(e) e.returnValue = window.leavePageTip;

            // Chrome, Safari, Firefox 4+, Opera 12+ , IE 9+
            return window.leavePageTip;
        }
    });

    setAutoHeight();
    $(window).on('resize', setAutoHeight);
});

/**
 * Get FlowChart object instance
 * @return {FlowChart}
 */
function getFlowchart()
{
    return $('#editorCanvas').data('zui.flowChart');
}

/**
 * Save flowChart data
 * @param {function} [callback] Callback for save complete
 * @param {boolean} [force] Force save and ignore changes status
 * @return {Object[]} Flowchart elements list
 */
function saveFlowchart(callback, force)
{
    var flowChart = getFlowchart();
    if(!force && !flowChart.changed) return;

    var data = flowChart.exportData();
    data.forEach(function(elementData) {delete elementData._saved;});
    var link = createLink('workflow', 'ajaxSaveFlowchart', 'module=' + window.moduleName);
    $.post(link, {'data':JSON.stringify(data)}, function(response)
    {
        try {response = JSON.parse(response);}
        catch(error)
        {
            response = response ? response : window.unknownError;
            new $.zui.Messager(response,
            {
                type:      'danger',
                icon:      'bell',
                placement: 'center',
                time:       10000
            }).show();
            if(callback) callback(false, response);
            return;
        }

        if(response.result == 'fail')
        {
            if($.type(response.message) == 'object')
            {
                for(var controlID in response.message)
                {
                    var $element = $('#editorCanvas-' + controlID);
                    var tooltip = $element.data('zui.tooltip');
                    if(tooltip && tooltip.show)
                    {
                        tooltip.hoverState = 'in';
                        tooltip.show(removeHtmlTag(response.message[controlID]));
                    }
                    else $element.attr('data-toggle', 'tooltip').tooltip({tipClass: 'tooltip-danger', tipId: 'tooltip-' + controlID}).tooltip('show', removeHtmlTag(response.message[controlID]));
                }
            } else if($.type(response.message) == 'string') bootbox.alert(removeHtmlTag(response.message));

            if(callback) callback(false, response);
        }
        else
        {
            var flowChartData = JSON.parse(response.flowchart);
            flowChartData.forEach(function(element)
            {
                element._saved = true;
            });
            flowChart.resetData(flowChartData);

            flowChart.changed = false;
            $('#saveBtn').popover(
            {
                trigger:   'manual',
                content:   response.message,
                tipClass:  'popover-success popover-form-result',
                placement: 'left'
            }).popover('show');

            setTimeout(function(){$('#saveBtn').popover('destroy')}, 2000);

            if($.isFunction(callback)) callback(true, response);
        }
    });
    return true;
}
