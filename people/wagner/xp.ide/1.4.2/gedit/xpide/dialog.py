
import gobject
import gtk
import os

class Calltip():
    def __init__(self, dialog, master, title=None):
        self._builder= gtk.Builder()
        self._builder.add_from_file(os.path.join(os.path.dirname(__file__), "dialog", dialog + ".ui"))
        self._dialog= self._builder.get_object(dialog)
        if master: self._dialog.set_transient_for(master)
        if title:  self._dialog.set_title(title)

    def run(self):
        self._dialog.show_all()
        ret= self._dialog.run()
        self._dialog.hide()
        return ret

class TextSelectCalltip(Calltip):
    def __init__(self, master, title=None):
        Calltip.__init__(self, "TextSelectCalltip", master, title)

        list_col= gtk.TreeViewColumn(None, gtk.CellRendererText(), text= 0)
        list_col.set_sort_order(gtk.SORT_ASCENDING)
        self._list= gtk.ListStore(gobject.TYPE_STRING)
        self._view= self._builder.get_object("suggestions")
        self._view.set_model(self._list)
        self._view.append_column(list_col)
        self._view.connect("key-press-event", self.keypressEH)
        self._view.connect("button-press-event", self.buttonpressEH)
    
    def addOption(self, text):
        self._list.set(self._list.append(), 0, text)
        return self
        
    def run(self):
        ret= Calltip.run(self)
        if (ret != gtk.RESPONSE_OK): return None
        ret_iter= self._view.get_selection().get_selected()[1]
        if (ret_iter is None): return None
        return self._list.get(ret_iter, 0)[0]

    def keypressEH(self, widget, event):
        if (gtk.gdk.keyval_name(event.keyval) in ["Return", "space"]):
            self._dialog.response(gtk.RESPONSE_OK);
            return True
        return False

    def buttonpressEH(self, widget, event):
        if (gtk.gdk._2BUTTON_PRESS == event.type):
            self._dialog.response(gtk.RESPONSE_OK);
            return True
        return False

class MakeAccessor(Calltip):
    def __init__(self, master, title=None):
        Calltip.__init__(self, "MakeAccessor", master, title)

        list_col_name= gtk.TreeViewColumn("Name", gtk.CellRendererText(), text= 0)
        list_col_name.set_sort_order(gtk.SORT_ASCENDING)

        list_col_type= gtk.TreeViewColumn("Type", gtk.CellRendererText(), text= 1)

        self._renderer_set= gtk.CellRendererToggle()
        self._renderer_set.connect("toggled", self.toggleCol, 2)
        list_col_setter= gtk.TreeViewColumn("set", self._renderer_set, active= 2)

        self._renderer_get= gtk.CellRendererToggle()
        self._renderer_get.connect("toggled", self.toggleCol, 3)
        list_col_getter= gtk.TreeViewColumn("get", self._renderer_get, active= 3)

        self._renderer_extendedtype= gtk.CellRendererText()
        self._renderer_extendedtype.set_property("editable", True)
        self._renderer_extendedtype.connect("edited", self.editCol, 4)
        list_col_extendedtype= gtk.TreeViewColumn("extended Type", self._renderer_extendedtype, text= 4, visible= 5)

        self._renderer_dim= gtk.CellRendererSpin()
        self._renderer_dim.set_property("editable", True)
        list_col_dim= gtk.TreeViewColumn("dimension", self._renderer_dim, adjustment= 6, visible= 7)
        list_col_dim.set_cell_data_func(self._renderer_dim, self.adjustCol)

        self._list= gtk.ListStore(
            gobject.TYPE_STRING, 
            gobject.TYPE_STRING,
            gobject.TYPE_BOOLEAN,
            gobject.TYPE_BOOLEAN,
            gobject.TYPE_STRING,
            gobject.TYPE_BOOLEAN,
            gobject.TYPE_OBJECT,
            gobject.TYPE_BOOLEAN
        )
        self._view= self._builder.get_object("members")
        self._view.set_model(self._list)
        self._view.append_column(list_col_name)
        self._view.append_column(list_col_type)
        self._view.append_column(list_col_setter)
        self._view.append_column(list_col_getter)
        self._view.append_column(list_col_extendedtype)
        self._view.append_column(list_col_dim)

    def toggleCol(self, cell, path, col):
        self._list[path][col]= not self._list[path][col]
        return

    def editCol(self, cell, path, newval, col):
        self._list[path][col]= newval
        return

    def adjustCol(self, col, cell, model, miter):
        cell.set_property('text', int(cell.get_property('adjustment').get_value()))
        return

    def addMember(self, text, type, setter, getter):
        xtype= type in ['object', 'array']
        xtype_init= ''
        if (xtype): xtype_init= 'lang.Object'

        dim= type in ['array']
        dim_init= gtk.Adjustment(0 ,0, 0, 0, 0)
        if (dim): dim_init.set_all(1, 1, 10, 1, 1)

        self._list.set(self._list.append([text, type, setter, getter, xtype_init, xtype, dim_init, dim]))
        return self

    def run(self):
        ret= Calltip.run(self)
        if (0 != ret): return None
        return self._list

class TextCalltip(Calltip):
    def __init__(self, master, title=None):
        Calltip.__init__(self, "TextCalltip", master, title)
        self._hint= self._builder.get_object("hint")

    def setText(self, text):
        self._hint.set_text(text)
        return self