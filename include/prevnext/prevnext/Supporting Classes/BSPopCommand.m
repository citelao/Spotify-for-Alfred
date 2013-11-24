//
//  BSPopCommand.m
//  prevnext
//
//  Created by Ben Stolovitz on 11/20/13.
//  Copyright (c) 2013 Ben Stolovitz. All rights reserved.
//

#import "BSPopCommand.h"

@implementation BSPopCommand

// https://developer.apple.com/library/mac/documentation/cocoa/conceptual/ScriptableCocoaApplications/SApps_handle_AEs/SAppsHandleAEs.html#//apple_ref/doc/uid/20001239-1134778
// http://stackoverflow.com/questions/2479585/how-do-i-add-applescript-support-to-my-cocoa-application
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
    
    // http://stackoverflow.com/questions/842737/cocoa-notification-example
    // because I'm bad at Cocoa.
    [[NSNotificationCenter defaultCenter] postNotificationName:@"ApplicationShouldDisplayImage" object:stringToSearch];
    return nil;
}

@end
