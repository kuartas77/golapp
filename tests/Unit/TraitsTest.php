<?php

test('fields', function () {


    $result = 1+2;

    // expect($result)->toBeString();
    $this->assertSame(3, $result);
});