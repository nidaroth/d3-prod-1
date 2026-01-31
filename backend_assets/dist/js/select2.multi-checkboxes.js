/**
 * jQuery Select2 Multi checkboxes
 * - allow to select multi values via normal dropdown control
 * 
 * author      : wasikuss
 * repo        : hhttps://github.com/wasikuss/select2-multi-checkboxes/tree/amd
 * inspired by : https://github.com/select2/select2/issues/411
 * License     : MIT
 */

(function($) {
  // build: commit c09a8f6 
  !function(e){"function"==typeof define&&define.amd?define(["jquery"],e):e(jQuery)}(function(e){var t;e&&e.fn&&e.fn.select2&&e.fn.select2.amd&&(t=e.fn.select2.amd.define),t("select2/multi-checkboxes/dropdown",["select2/utils","select2/dropdown","select2/dropdown/search","select2/dropdown/attachBody"],function(e,t,n,s){return e.Decorate(e.Decorate(t,n),s)}),t("select2/multi-checkboxes/results",["jquery","select2/utils","select2/results"],function(l,e,t){function n(){n.__super__.constructor.apply(this,arguments)}return e.Extend(n,t),n.prototype.highlightFirstItem=function(){this.ensureHighlightVisible()},n.prototype.bind=function(e){e.on("open",function(){var e=this.$results.find(".select2-results__option[aria-selected]").filter("[aria-selected=true]");(e.length,e).first().trigger("mouseenter")}),n.__super__.bind.apply(this,arguments)},n.prototype.template=function(e,t){var n=this.options.get("templateResult"),s=this.options.get("escapeMarkup"),i=n(e,t);l(t).addClass("multi-checkboxes_wrap"),null==i?t.style.display="none":"string"==typeof i?t.innerHTML=s(i):l(t).append(i)},n}),t("select2/multi-checkboxes/selection",["select2/utils","select2/selection/multiple","select2/selection/placeholder","select2/selection/single","select2/selection/eventRelay"],function(e,t,n,s,i){var l=e.Decorate(t,n);return(l=e.Decorate(l,i)).prototype.render=function(){return s.prototype.render.call(this)},l.prototype.update=function(e){var t=this.$selection.find(".select2-selection__rendered"),n="";if(0===e.length)n=this.options.get("placeholder")||"";else{var s={selected:e||[],all:this.$element.find("option")||[]};n=this.display(s,t)}t.empty().append(n),t.prop("title",n)},l})});
  //
  
  
    $.fn.select2.amd.require(
      [
        'select2/multi-checkboxes/dropdown',
        'select2/multi-checkboxes/selection',
        'select2/multi-checkboxes/results'
      ],
      function(DropdownAdapter, SelectionAdapter, ResultsAdapter) {
        $('.select2-original').select2({
          placeholder: 'Select items',
          width: "100%"
        });
  
        $('.first_term').select2({
          placeholder: 'Select First Term',
          closeOnSelect: false,
          minimumResultsForSearch:2,
          templateSelection: function(data) {
            if(data.selected[0].text!="ALL"){
              $(".first_term > option:nth(0)").prop("selected","");
            }
            return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
          },
          dropdownAdapter: DropdownAdapter,
          selectionAdapter: SelectionAdapter,
          resultsAdapter: ResultsAdapter
         
        }).on('select2:select', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
            $(".first_term > option").prop("selected",'selected');
            $("#select2-PK_TERM_MASTER-results li").each(function(e,item){
              $("#"+item.id).attr('aria-selected',true)
            });
            setTimeout(function(){
              $(".first_term").trigger("change");// Trigger change to select 2
              $("#loaders").css('display','none');

            },300)

          }
        }).on('select2:unselect', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
              $(".first_term > option").prop("selected","");
              $("#select2-PK_TERM_MASTER-results li").each(function(e,item){
                $("#"+item.id).attr('aria-selected',false)
              });
              setTimeout(function(){
                $(".first_term").trigger("change");// Trigger change to select 2
                $("#loaders").css('display','none');
  
              },300)

            }
        }).
        on('select2:mouseenter',function(e){
          // $("#loaders").css('display','block');
        document.getElementById("loaders").style.display="block";
           //console.log('1');
 
         }).on('select2:opening',function(e){
         // $("#loaders").css('display','block');
       document.getElementById("loaders").style.display="block";
          //console.log('1');

        }).on('select2:open',function(e){
          setTimeout(function(){        
            $("#loaders").css('display','none');
        },400);
        //console.log('2');
        })
      
        $('#PK_STUDENT_STATUS').select2({
          placeholder: 'Select Student Status',
          closeOnSelect: false,
          minimumResultsForSearch:2,
          templateResult: function (data, container) {
            if (data.text.match('(Inactive)')!==null) {
                $(container).css({"color":'red'});
            }
            return data.text;
          },
          templateSelection: function(data,container) {
            if(data.selected[0].text!="ALL"){
              $("#PK_STUDENT_STATUS > option:nth(0)").prop("selected","");
            }
           


            return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
          },
          dropdownAdapter: DropdownAdapter,
          selectionAdapter: SelectionAdapter,
          resultsAdapter: ResultsAdapter
        }).on('select2:select', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
            $("#PK_STUDENT_STATUS > option").prop("selected",'selected');
            $("#select2-PK_STUDENT_STATUS-results li").each(function(e,item){
              $("#"+item.id).attr('aria-selected',true)
            });
            setTimeout(function(){
              $("#PK_STUDENT_STATUS").trigger("change");// Trigger change to select 2
              $("#loaders").css('display','none');

            },300)

          }
        }).on('select2:unselect', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
              $("#PK_STUDENT_STATUS > option").prop("selected","");
              $("#select2-PK_STUDENT_STATUS-results li").each(function(e,item){
                $("#"+item.id).attr('aria-selected',false)
              });
              setTimeout(function(){
                $("#PK_STUDENT_STATUS").trigger("change");// Trigger change to select 2
                $("#loaders").css('display','none');
  
              },300)

            }
        })

        $('#PK_CAMPUS').select2({
          placeholder: 'Select Campus',
          closeOnSelect: false,
          minimumResultsForSearch:2,
          templateSelection: function(data) {
            if(data.selected[0].text!="ALL"){
              $("#PK_CAMPUS > option:nth(0)").prop("selected","");
            }
            return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
          },
          dropdownAdapter: DropdownAdapter,
          selectionAdapter: SelectionAdapter,
          resultsAdapter: ResultsAdapter
        }).on('select2:select', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
            $("#PK_CAMPUS > option").prop("selected",'selected');
            $("#select2-PK_CAMPUS-results li").each(function(e,item){
              $("#"+item.id).attr('aria-selected',true)
            });
            setTimeout(function(){
              $("#PK_CAMPUS").trigger("change");// Trigger change to select 2
              $("#loaders").css('display','none');

            },300)

          }
        }).on('select2:unselect', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
              $("#PK_CAMPUS > option").prop("selected","");
              $("#select2-PK_CAMPUS-results li").each(function(e,item){
                $("#"+item.id).attr('aria-selected',false)
              });
              setTimeout(function(){
                $("#PK_CAMPUS").trigger("change");// Trigger change to select 2
                $("#loaders").css('display','none');
  
              },300)

            }
        })
         /** Program filter */ 
        $('#PK_CAMPUS_PROGRAM').select2({
          placeholder: 'Select Program',
          closeOnSelect: false,
          minimumResultsForSearch:2,
          templateResult: function (data, container) {
            if (data.text.match('(Inactive)')!==null) {
                $(container).css({"color":'red'});
            }
            return data.text;
          },
          templateSelection: function(data) {
            if(data.selected[0].text!="ALL"){
              $("#PK_CAMPUS_PROGRAM > option:nth(0)").prop("selected","");
            }
            return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
          },
          dropdownAdapter: DropdownAdapter,
          selectionAdapter: SelectionAdapter,
          resultsAdapter: ResultsAdapter
        }).on('select2:select', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
            $("#PK_CAMPUS_PROGRAM > option").prop("selected",'selected');
            $("#select2-PK_CAMPUS_PROGRAM-results li").each(function(e,item){
              $("#"+item.id).attr('aria-selected',true)
            });
            setTimeout(function(){
              $("#PK_CAMPUS_PROGRAM").trigger("change");// Trigger change to select 2
              $("#loaders").css('display','none');

            },300)

          }
        }).on('select2:unselect', function (e) {
          if(e.params.data.id=="0"){
            $("#loaders").css('display','block');
              $("#PK_CAMPUS_PROGRAM > option").prop("selected","");
              $("#select2-PK_CAMPUS_PROGRAM-results li").each(function(e,item){
                $("#"+item.id).attr('aria-selected',false)
              });
              setTimeout(function(){
                $("#PK_CAMPUS_PROGRAM").trigger("change");// Trigger change to select 2
                $("#loaders").css('display','none');
  
              },300)

            }
        })

                 /** Funding filter */ 
                 $('#PK_FUNDING').select2({
                  placeholder: 'Select Funding',
                  closeOnSelect: false,
                  minimumResultsForSearch:2,
                  templateResult: function (data, container) {
                    if (data.text.match('(Inactive)')!==null) {
                        $(container).css({"color":'red'});
                    }
                    return data.text;
                  },
                  templateSelection: function(data) {
                    if(data.selected[0].text!="ALL"){
                      $("#PK_FUNDING > option:nth(0)").prop("selected","");
                    }
                    return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
                  },
                  dropdownAdapter: DropdownAdapter,
                  selectionAdapter: SelectionAdapter,
                  resultsAdapter: ResultsAdapter
                }).on('select2:select', function (e) {
                  if(e.params.data.id=="0"){
                    $("#loaders").css('display','block');
                    $("#PK_FUNDING > option").prop("selected",'selected');
                    $("#select2-PK_FUNDING-results li").each(function(e,item){
                      $("#"+item.id).attr('aria-selected',true)
                    });
                    setTimeout(function(){
                      $("#PK_FUNDING").trigger("change");// Trigger change to select 2
                      $("#loaders").css('display','none');
        
                    },300)
        
                  }
                }).on('select2:unselect', function (e) {
                  if(e.params.data.id=="0"){
                    $("#loaders").css('display','block');
                      $("#PK_FUNDING > option").prop("selected","");
                      $("#select2-PK_FUNDING-results li").each(function(e,item){
                        $("#"+item.id).attr('aria-selected',false)
                      });
                      setTimeout(function(){
                        $("#PK_FUNDING").trigger("change");// Trigger change to select 2
                        $("#loaders").css('display','none');
          
                      },300)
        
                    }
                })

                           /** Course filter */ 
                           $('#PK_COURSE').select2({
                            placeholder: 'Select Course',
                            closeOnSelect: false,
                            minimumResultsForSearch:2,
                            templateSelection: function(data) {
                              if(data.selected[0].text!="ALL"){
                                $("#PK_COURSE > option:nth(0)").prop("selected","");
                              }
                              return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
                            },
                            dropdownAdapter: DropdownAdapter,
                            selectionAdapter: SelectionAdapter,
                            resultsAdapter: ResultsAdapter
                          }).on('select2:select', function (e) {
                            //$("#loaders").css('display','block');
                            if(e.params.data.id=="0"){
                              $("#loaders").css('display','block');

                              $("#PK_COURSE > option").prop("selected",'selected');
                              $("#select2-PK_COURSE-results li").each(function(e,item){
                                $("#"+item.id).attr('aria-selected',true)
                              });
                              //$("#PK_COURSE").trigger("change");
                              setTimeout(function(){
                                $("#PK_COURSE").trigger("change");// Trigger change to select 2
                                $("#loaders").css('display','none');
                  
                              },300);
                            }
                            setTimeout(function(){
                            var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0&page=student_invoice';
                            $.ajax({
                             url: "ajax_get_course_offering",	
                             type: "POST",		 
                             data: data,		
                             async: false,
                             cache: false,
                             success: function (data) {	
                               $("#loaders").css('display','none');
                               document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
                               document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
                               document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
                               $("#PK_COURSE_OFFERING option[value='']").remove();
                               
                               //document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_search()");
                               $('.course_offering').select2({
                                placeholder: 'Select Course Offering',
                                closeOnSelect: false,
                                minimumResultsForSearch:2,
                                templateSelection: function(data) {
                                  if(data.selected[0].text!="ALL"){
                                    $(".course_offering > option:nth(0)").prop("selected","");
                                  }
                                  return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
                                },
                                dropdownAdapter: DropdownAdapter,
                                selectionAdapter: SelectionAdapter,
                                resultsAdapter: ResultsAdapter
                              }).on('select2:select', function (e) {
                                if(e.params.data.id=="0"){
                                  $("#loaders").css('display','block');
                                  $(".course_offering > option").prop("selected",'selected');
                                  $("#select2-PK_COURSE_OFFERING-results li").each(function(e,item){
                                    $("#"+item.id).attr('aria-selected',true)
                                  });
                                  setTimeout(function(){
                                    $(".course_offering").trigger("change");// Trigger change to select 2
                                    $("#loaders").css('display','none');
                      
                                  },300)
                      
                                }
                              }).on('select2:unselect', function (e) {
                                if(e.params.data.id=="0"){
                                  $("#loaders").css('display','block');
                                    $(".course_offering > option").prop("selected","");
                                    $("#select2-PK_COURSE_OFFERING-results li").each(function(e,item){
                                      $("#"+item.id).attr('aria-selected',false)
                                    });
                                    setTimeout(function(){
                                      $(".course_offering").trigger("change");// Trigger change to select 2
                                      $("#loaders").css('display','none');
                        
                                    },300)
                      
                                  }
                              }).on('select2:change',function(e){
                                // $("#loaders").css('display','block');
                              document.getElementById("loaders").style.display="block";
                                 //console.log('1');
                       
                               }).on('select2:opening',function(e){
                               // $("#loaders").css('display','block');
                             document.getElementById("loaders").style.display="block";
                                //console.log('1');
                      
                              }).on('select2:open',function(e){
                                setTimeout(function(){        
                                  $("#loaders").css('display','none');
                              },400);
                              //console.log('2');
                              })
                               
                             }		
                           });
                          },200);

                          }).on('select2:unselect', function (e) {
                            if(e.params.data.id=="0"){
                              $("#loaders").css('display','block');
                                $("#PK_COURSE > option").prop("selected","");
                                $("#select2-PK_COURSE-results li").each(function(e,item){
                                  $("#"+item.id).attr('aria-selected',false)
                                });
                                setTimeout(function(){
                                  $("#PK_COURSE").trigger("change");// Trigger change to select 2
                                  $("#loaders").css('display','none');
                    
                                },300)
                  
                              }
                            setTimeout(function(){
                              var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0&page=student_invoice';
                              $.ajax({
                               url: "ajax_get_course_offering",	
                               type: "POST",		 
                               data: data,		
                               async: false,
                               cache: false,
                               success: function (data) {	
                                 $("#loaders").css('display','none');
                                 document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
                                 document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
                                 document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
                                 $("#PK_COURSE_OFFERING option[value='']").remove();
                                 
                                 //document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_search()");
                                 $('.course_offering').select2({
                                  placeholder: 'Select Course Offering',
                                  closeOnSelect: false,
                                  minimumResultsForSearch:2,
                                  templateSelection: function(data) {
                                    return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
                                  },
                                  dropdownAdapter: DropdownAdapter,
                                  selectionAdapter: SelectionAdapter,
                                  resultsAdapter: ResultsAdapter
                                }).on('select2:select', function (e) {
                                  if(e.params.data.id=="0"){
                                    $("#loaders").css('display','block');
                                    $(".course_offering > option").prop("selected",'selected');
                                    $("#select2-PK_COURSE_OFFERING-results li").each(function(e,item){
                                      $("#"+item.id).attr('aria-selected',true)
                                    });
                                    setTimeout(function(){
                                      $(".course_offering").trigger("change");// Trigger change to select 2
                                      $("#loaders").css('display','none');
                        
                                    },300)
                        
                                  }
                                }).on('select2:unselect', function (e) {
                                  if(e.params.data.id=="0"){
                                    $("#loaders").css('display','block');
                                      $(".course_offering > option").prop("selected","");
                                      $("#select2-PK_COURSE_OFFERING-results li").each(function(e,item){
                                        $("#"+item.id).attr('aria-selected',false)
                                      });
                                      setTimeout(function(){
                                        $(".course_offering").trigger("change");// Trigger change to select 2
                                        $("#loaders").css('display','none');
                          
                                      },300)
                        
                                    }
                                })
                                 
                               }		
                             });
                            },200)
                              
                          })

                          // $('.course_offering').select2({
                          //   placeholder: 'Select Course Offering',
                          //   closeOnSelect: false,
                          //   minimumResultsForSearch:2,
                          //   templateSelection: function(data) {
                          //     return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
                          //   },
                          //   dropdownAdapter: DropdownAdapter,
                          //   selectionAdapter: SelectionAdapter,
                          //   resultsAdapter: ResultsAdapter
                          // })



                          $('#PK_STUDENT_GROUP').select2({
                            placeholder: 'Select Student Group',
                            closeOnSelect: false,
                            minimumResultsForSearch:2,
                            templateSelection: function(data) {
                              if(data.selected[0].text!="ALL"){
                                $("#PK_STUDENT_GROUP > option:nth(0)").prop("selected","");
                              }
                              return 'Selected ' + data.selected.length + ' out of ' + data.all.length;
                            },
                            dropdownAdapter: DropdownAdapter,
                            selectionAdapter: SelectionAdapter,
                            resultsAdapter: ResultsAdapter
                          }).on('select2:select', function (e) {
                            if(e.params.data.id=="0"){
                              $("#loaders").css('display','block');
                              $("#PK_STUDENT_GROUP > option").prop("selected",'selected');
                              $("#select2-PK_STUDENT_GROUP-results li").each(function(e,item){
                                $("#"+item.id).attr('aria-selected',true)
                              });
                              setTimeout(function(){
                                $("#PK_STUDENT_GROUP").trigger("change");// Trigger change to select 2
                                $("#loaders").css('display','none');
                  
                              },300)
                  
                            }
                          }).on('select2:unselect', function (e) {
                            if(e.params.data.id=="0"){
                              $("#loaders").css('display','block');
                                $("#PK_STUDENT_GROUP > option").prop("selected","");
                                $("#select2-PK_STUDENT_GROUP-results li").each(function(e,item){
                                  $("#"+item.id).attr('aria-selected',false)
                                });
                                setTimeout(function(){
                                  $("#PK_STUDENT_GROUP").trigger("change");// Trigger change to select 2
                                  $("#loaders").css('display','none');
                    
                                },300)
                  
                              }
                          })


      }
    );
  }(jQuery));
