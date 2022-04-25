/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
/*global define*/
define([
    'jquery',
    'mage/url',
    'jquery/ui'
], function($,  url) {
    'use strict';
    return {
        addPacAttribute: function(selectedAttributeValue,ajaxUrl) {
            var self = this;
            $.ajax({
                showLoader: true,
                data: {
                    selectedVals: selectedAttributeValue
                },
                url: ajaxUrl,
                type: "POST",
                dataType:'json',
            }).done(function(data) {
                $( "#addToEndBtn_deis" ).trigger( "click" );
                var lastRowId = $('#addRow_deis tr:last').attr('id');
                var headingId = lastRowId+'_header';
                var labelValue = data.label;
                var datatype = data.datatype;
                var pacattribute = data.pacattribute;
                var pacattributeName = data.pacattributeName;
                var attributeDescription = data.attributeDescription;
                var mappingTable =  'groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][index]';
                var renderer = 'groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][renderer]';
                var typeoptions ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][type]';
                var pacoptions ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][options]';
                var filteroptions ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][filter_by]';
                var pacAttributes = lastRowId+'_pacattributes';
                var pacDataTypeJson = lastRowId+'_datatypejson';
                self.mapPacTable(mappingTable,pacattribute,pacattributeName);
                if(datatype =="combobox") {
                   self.typeOptions(typeoptions,'options');
                   self.pacOptions(pacoptions);
                } else if(datatype =="integer") {
                   self.typeOptions(typeoptions,'number');
                } else if(datatype =="character") {
                   self.typeOptions(typeoptions,'text');
                } else if(datatype =="date") {
                   self.typeOptions(typeoptions,'date');
                }  else if(datatype =="decimal") {
                   self.typeOptions(typeoptions,'number');
                }  else if(datatype =="checkbox") {
                   self.typeOptions(typeoptions,'options');
                   self.pacOptions(pacoptions);
                }  else {
                   self.typeOptions(typeoptions,'text'); 
                }
                self.filterByOptions(filteroptions);
                $('#'+headingId).val(attributeDescription);
                $('#'+pacAttributes).val(data.pacattributeid);
                $('#'+pacAttributes).attr("rel", data.pacattributeid);
                $('#'+pacDataTypeJson).val(JSON.stringify(data));
                $("#grid_deis #addRow_deis").tableDnDUpdate();
                $("#grid_deis #addRow_deis tr:even").addClass("altdeis");                
            });   
        },
        addDeidPacAttribute: function(selectedAttributeValue,ajaxUrl) {
            var self = this;
            $.ajax({
                showLoader: true,
                data: {
                    selectedVals: selectedAttributeValue
                },
                url: ajaxUrl,
                type: "POST",
                dataType:'json',
            }).done(function(data) {
                $( "#addToEndBtn_deidinformation" ).trigger( "click" );
                var lastRowId = $('#addRow_deidinformation tr:last').attr('id');
                var headingId = lastRowId+'_header';
                var pacDataTypeJson = lastRowId+'_hiddenpac';
                var mappingTable =  'groups[DEID_request][fields][grid_informationconfig][value]['+lastRowId+'][index]';
                var pacattributeid = data.pacattributeid;
                var pacattributeName = data.pacattributeName;
                var attributeDescription = data.attributeDescription;
                var indexId = lastRowId+'_index';
                $('#'+headingId).val(attributeDescription);
                self.mapDeidPacTable(mappingTable,pacattributeid,pacattributeName);
                $('#'+pacDataTypeJson).val(JSON.stringify(data));
                $('#'+indexId).attr("rel",pacattributeid);
                $("#grid_deidinformation #addRow_deidinformation").tableDnDUpdate();
                $("#grid_deidinformation #addRow_deidinformation tr:even").addClass("altdeis");                    
            });   
        },        
        removePacAttribute: function(selectedAttributeValue) {
            $("#grid_deis input.pac_attribute_value").each(function () {
                 var lastRowId = $(this).attr("rel");
                 if(selectedAttributeValue == lastRowId) {
                     var row = $(this).closest("tr");
                     var getId = row.attr('id');
                     $('#grid_deis tr#'+getId).remove();
                 }
                
            });
        },
        removeDeidInfoPacAttribute: function(selectedAttributeValue) {
            $("#grid_deidinformation select.deidinfomappingrenderer").each(function () {
                 var lastRowId = $(this).attr("rel");
                 if(selectedAttributeValue == lastRowId) {
                     var row = $(this).closest("tr");
                     var getId = row.attr('id');
                     $('#grid_deidinformation tr#'+getId).remove();
                 }
                
            });
        },        
        loadPacAttributes: function() {
            var self = this;
            $("#grid_deis #addRow_deis tr").each(function () {
                var lastRowId = $(this).attr("id");
                var mappingTable =  'groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][index]';
                var renderer = 'groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][renderer]';
                var typeoptions ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][type]';         
                var pacoptions ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][options]';
                var filteroptions ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][filter_by]';
                //var visible ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][visible]';
                //var showfilter ='groups[DEIS_request][fields][grid_config][value]['+lastRowId+'][showfilter]';
                var dataTypeJson = $('#'+lastRowId+'_datatypejson').val();   
                if(dataTypeJson!='') {
                    var json_obj = $.parseJSON(dataTypeJson);//parse JSON
                    var datatype = json_obj.datatype;
                    var pacattribute = json_obj.pacattribute;
                    var pacattributeName = json_obj.pacattributeName;
                    self.mapPacTable(mappingTable,pacattribute,pacattributeName);
                    if(datatype =="combobox") {
                       self.typeOptions(typeoptions,'options');
                       self.pacOptions(pacoptions);
                    } else if(datatype =="integer") {
                       self.typeOptions(typeoptions,'number');
                    } else if(datatype =="character") {
                       self.typeOptions(typeoptions,'text');
                    } else if(datatype =="date") {
                       self.typeOptions(typeoptions,'date');
                    }  else if(datatype =="decimal") {
                       self.typeOptions(typeoptions,'number');
                    }  else if(datatype =="checkbox") {
                       self.typeOptions(typeoptions,'options');
                       self.pacOptions(pacoptions);
                    }  else {
                       self.typeOptions(typeoptions,'text'); 
                    }
                    self.filterByOptions(filteroptions);
                }
                self.loadDeisType();
                self.loadVisibility();
                self.loadShowFilter();
                self.loadDeisFilterBy();
                self.loadDeisCondition();
                self.loadDeisSortBy();
            });              
        },
        loadDeidPacAttributes: function() {
            var self = this;
            $("#grid_deidinformation #addRow_deidinformation tr").each(function () {
                var lastRowId = $(this).attr("id");
                var mappingTable =  'groups[DEID_request][fields][grid_informationconfig][value]['+lastRowId+'][index]';
                var relindex = lastRowId+'_index';
                var labelValue = $('#'+relindex).attr('rel');
                var hiddenpac = $('#'+lastRowId+'_hiddenpac').val();
                if(hiddenpac !="") {
                    var dataTypeJson = hiddenpac;   
                    var json_obj = $.parseJSON(dataTypeJson);//parse JSON
                    var pacattributeid = json_obj.pacattributeid;
                    var pacattributeName = json_obj.pacattributeName;
                    self.mapDeidPacTable(mappingTable,pacattributeid,pacattributeName);
                }
            });              
        },        
        loadDeisType:function() {
            $("#addRow_deis select.deistype").each(function () {
                 var lastRowId = $(this).attr("rel");
                 var lastRowName = $(this).attr("name");
                 $('select[name="'+lastRowName+'"]').val(lastRowId);
            });            
        },        
        loadVisibility:function() {
            $("#addRow_deis select.deisvisible").each(function () {
                 var lastRowId = $(this).attr("rel");
                 var lastRowName = $(this).attr("name");
                 $('select[name="'+lastRowName+'"]').val(lastRowId);
            });            
        },
        loadShowFilter:function() {
            $("#addRow_deis select.deisshowfilter").each(function () {
                 var lastRowId = $(this).attr("rel");
                 var lastRowName = $(this).attr("name");
                 $('select[name="'+lastRowName+'"]').val(lastRowId);
            });            
        }, 
        loadDeisFilterBy:function() {
            $("#addRow_deis select.deisfilter_by").each(function () {
                 var lastRowId = $(this).attr("rel");
                 var lastRowName = $(this).attr("name");
                 $('select[name="'+lastRowName+'"]').val(lastRowId);
            });            
        },  
        loadDeisCondition:function() {
            $("#addRow_deis select.deiscondition").each(function () {
                 var lastRowId = $(this).attr("rel");
                 var lastRowName = $(this).attr("name");
                 $('select[name="'+lastRowName+'"]').val(lastRowId);
            });            
        },   
        loadDeisSortBy:function() {
            $("#addRow_deis select.deissortby").each(function () {
                 var lastRowId = $(this).attr("rel");
                 var lastRowName = $(this).attr("name");
                 $('select[name="'+lastRowName+'"]').val(lastRowId);
            });            
        },           
        typeOptions: function(typeoptions,selectedValues) {
            $('select[name="'+typeoptions+'"]').val(selectedValues);
            //$('select[name="'+typeoptions+'"]').attr("style", "pointer-events: none;");            
        },
        pacOptions: function(pacoptions) {
            $('select[name="'+pacoptions+'"]').append('<option value="dealerconnect/erp_mapping_pac" selected="selected">PAC</option>');
            $('select[name="'+pacoptions+'"]').attr("style", "pointer-events: none;");                 
        },
        filterByOptions: function(pacoptions) {
            $('select[name="'+pacoptions+'"]').val("erp");
            $('select[name="'+pacoptions+'"]').attr("style", "pointer-events: none;");                 
        },        
        mapPacTable: function(mappingId,pacattribute,pacattributeName) {
            $('select[name="'+mappingId+'"]').append('<option value="pac_attributes_attribute_code_'+pacattribute+'" selected="selected">'+pacattributeName+'</option>');
            $('select[name="'+mappingId+'"]').attr("style", "pointer-events: none;width:151px");            
        },
        mapDeidPacTable: function(mappingId,pacattribute,pacattributeName) {
            $('select[name="'+mappingId+'"]').append('<option value="'+pacattribute+'" selected="selected">'+pacattributeName+'</option>');
            $('select[name="'+mappingId+'"]').attr("style", "pointer-events: none;width:50%;");            
        }        
    };
});