/* mboc = MBO helper functions for Chart js library */
/* loaded with mbo plugin */
/* takes a string phrase and breaks   it into separate phrases 
   no bigger than 'maxwidth', breaks are made at complete words.*/

   function mboc_formatLabel(str, maxwidth){
    var sections = [];
    var words = str.split(" ");
    var temp = "";

    words.forEach(function(item, index){
        if(temp.length > 0) {
            var concat = temp + ' ' + item;

            if(concat.length > maxwidth){
                sections.push(temp);
                temp = "";
            } else {
                if(index == (words.length-1)) {
                    sections.push(concat);
                    return;
                }
                temp = concat;
                return;
            }
        }

        if(index == (words.length-1)) {
            sections.push(item);
            return;
        }

        if(item.length < maxwidth)
            temp = item;
        else
            sections.push(item);
    });

    return sections;
}