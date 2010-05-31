    /**
     * Method to publish / unpublish an object
     *
     * @return void
     * @access public
     * @since  1.0
     */
    public function publish()
    {
        $publish = ($this->getTask() == 'publish' ? 1 : 0);
        $cid = JRequest::getVar('cid', array(0), 'post', 'array');
        JArrayHelper::toInteger($cid, array(0));
        if (count($cid) > 0) {
            $table =& $this->getModel('{{controller}}')->getTable('{{controller}}', 'JTable');
            $table->publish($cid, $publish);
        }
        $this->setRedirect('index.php?option={{component}}&view={{controller}}&layout=list');
    }
