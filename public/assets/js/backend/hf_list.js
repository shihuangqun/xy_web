define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'hf_list/index' + location.search,
                    add_url: 'hf_list/add',
                    edit_url: 'hf_list/edit',
                    del_url: 'hf_list/del',
                    multi_url: 'hf_list/multi',
                    import_url: 'hf_list/import',
                    table: 'hf_list',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'member_id', title: __('Member_id')},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'order_num', title: __('Order_num'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 're_status', title: __('Re_status'), searchList: {"0":__('Re_status 0'),"1":__('Re_status 1')}, formatter: Table.api.formatter.status},
                        {field: 'member.nickname', title: __('Member.nickname'), operate: 'LIKE'},
                        {field: 'member.avatar', title: __('Member.avatar'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});