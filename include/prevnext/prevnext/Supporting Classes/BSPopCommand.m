//
//  BSPopCommand.m
//  prevnext
//
//  Created by Ben Stolovitz on 11/20/13.
//  Copyright (c) 2013 Ben Stolovitz. All rights reserved.
//

#import "BSPopCommand.h"

@implementation BSPopCommand

-(id)performDefaultImplementation {
    NSDictionary *args = [self evaluatedArguments];
    NSString *stringToSearch = @"";
    if(args.count) { 
        stringToSearch = [args valueForKey:@""];    // get the direct argument
    } else {
        // raise error
        [self setScriptErrorNumber:-50];
        [self setScriptErrorString:@"Parameter Error: A Parameter is expected for the verb 'lookup' (You have to specify _what_ you want to lookup!)."];
    }
    
    [[NSNotificationCenter defaultCenter] postNotificationName:@"ApplicationShouldDisplayImage" object:stringToSearch];
    return nil;
}

@end
