/**
 * Created by zmx on 17/1/14.
 */
function empty(v)
{
    return v == undefined || v == null || v.trim() == "";
}

function val(v, def)
{
    return empty(v)? def: v;
}