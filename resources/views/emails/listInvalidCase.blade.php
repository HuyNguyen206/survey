<h2 style="text-align: center">Thông báo từ hệ thống CEM</h2>
<h4 style="text-align: center">{{$title}}</h4>

<table border="1" class="table table-striped" style="border-collapse: collapse; text-align: center">

    <thead>
    <th>STT</th>
      <th>SectionID</th>
    </thead>
    <tbody>
            <?php
            $i=1;
foreach ($info as $key => $value) {
    ?>
        <tr>
            <td>
              {{$i}}  
            </td>
            <td>
               {{$value}} 
            </td>
        </tr>
        <?php
        $i++;
}
?>
    </tbody>

</table>
