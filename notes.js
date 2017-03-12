function confirm_Delete(id){
    if (confirm('Are you sure you want to delete this notes? This action is irreversible.')) {
        window.open("?del=" + id,"_self");
    } else {
        // Do nothing
    }
}